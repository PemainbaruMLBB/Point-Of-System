<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TelegramService;

class SaleController extends Controller
{
    public function create()
    {
        $products = Product::with('saleUnits')
            ->where('is_active', true)
            ->where(fn ($query) => $query->where('type', 'service')->orWhere('stock', '>', 0))
            ->orderBy('name')
            ->get()
            ->map(function (Product $product): Product {
                $retail = $product->saleUnits->firstWhere('conversion', 1);
                $wholesale = $product->saleUnits->first(fn ($unit): bool => $unit->conversion > 1);
                $product->setRelation('saleUnits', collect([$retail, $wholesale])->filter()->values());

                return $product;
            });

        return view('sales.create', ['products' => $products, 'customers' => Customer::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['customer_id' => 'nullable|exists:customers,id', 'discount' => 'required|numeric|min:0', 'tax' => 'required|numeric|min:0', 'paid_amount' => 'required|numeric|min:0', 'payment_method' => 'required|in:cash,card,transfer,qris', 'items' => 'required|array|min:1', 'items.*.product_id' => 'required|exists:products,id', 'items.*.sale_unit_id' => 'required|exists:product_sale_units,id', 'items.*.quantity' => 'required|integer|min:1']);
        $sale = DB::transaction(function () use ($data, $request) {
            $subtotal = 0;
            $items = [];
            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $saleUnit = $product->saleUnits()->whereKey($item['sale_unit_id'])->firstOrFail();
                abort_if($product->type === 'product' && $product->stock < $item['quantity'] * $saleUnit->conversion, 422, "Stok {$product->name} tidak mencukupi.");
                $total = $saleUnit->selling_price * $item['quantity'];
                $subtotal += $total;
                $items[] = compact('product', 'saleUnit', 'total') + ['quantity' => $item['quantity']];
            }
            $customer = ! empty($data['customer_id']) ? Customer::findOrFail($data['customer_id']) : null;
            $discount = $customer ? round($subtotal * ($customer->discountPercent() / 100), 2) : 0;
            $total = $subtotal - $discount + $data['tax'];
            abort_if($data['paid_amount'] < $total, 422, 'Pembayaran kurang dari total transaksi.');
            $taxBase = max(0, $subtotal - $discount);
            $finalTaxRate = 0.50;
            $finalTaxAmount = round($taxBase * ($finalTaxRate / 100), 2);
            $sale = Sale::create(['invoice_number' => 'INV-'.now()->format('YmdHis').'-'.random_int(10, 99), 'user_id' => $request->user()->id, 'customer_id' => $data['customer_id'] ?? null, 'subtotal' => $subtotal, 'discount' => $discount, 'tax' => $data['tax'], 'tax_base' => $taxBase, 'final_tax_rate' => $finalTaxRate, 'final_tax_amount' => $finalTaxAmount, 'total' => $total, 'paid_amount' => $data['paid_amount'], 'change_amount' => $data['paid_amount'] - $total, 'payment_method' => $data['payment_method']]);
            foreach ($items as $item) {
                $sale->items()->create(['product_id' => $item['product']->id, 'quantity' => $item['quantity'], 'sale_unit_id' => $item['saleUnit']->id, 'unit_price' => $item['saleUnit']->selling_price, 'total' => $item['total']]);
                if ($item['product']->type === 'product') {
                    $stockQuantity = $item['quantity'] * $item['saleUnit']->conversion;
                    $item['product']->decrement('stock', $stockQuantity);
                    StockMovement::create(['product_id' => $item['product']->id, 'user_id' => $request->user()->id, 'type' => 'sale', 'quantity' => -$stockQuantity, 'stock_after' => $item['product']->fresh()->stock, 'reference' => $sale->invoice_number]);
                }
            }

            return $sale;
        });

        $sale->load('customer', 'items.product');
        app(TelegramService::class)->send($sale->customer?->telegram_opt_in ? $sale->customer->telegram_chat_id : null, "✅ <b>Transaksi berhasil</b>\nInvoice: {$sale->invoice_number}\nTotal: Rp ".number_format($sale->total, 0, ',', '.')."\nDiskon pelanggan: Rp ".number_format($sale->discount, 0, ',', '.'));
        $lowStock = $sale->items->filter(fn ($item) => $item->product->type === 'product' && $item->product->stock <= $item->product->minimum_stock)->pluck('product.name')->implode(', ');
        if ($lowStock !== '') {
            app(TelegramService::class)->admin("⚠️ <b>Stok menipis</b>\n{$lowStock}");
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Transaksi berhasil disimpan.');
    }

    public function show(Sale $sale)
    {
        return view('sales.show', ['sale' => $sale->load(['items.product', 'items.saleUnit', 'user', 'customer'])]);
    }

    public function updateTaxInvoice(Request $request, Sale $sale)
    {
        $data = $request->validate(['tax_invoice_number' => ['nullable', 'string', 'max:80']]);
        $sale->update($data);

        return back()->with('success', 'Nomor faktur pajak lanjutan diperbarui.');
    }
}

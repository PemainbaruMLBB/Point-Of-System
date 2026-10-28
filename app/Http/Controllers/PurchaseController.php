<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TelegramService;

class PurchaseController extends Controller
{
    public function create()
    {
        return view('purchases.create', ['products' => Product::where('type', 'product')->where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_name' => ['nullable', 'string', 'max:120'],
            'purchased_at' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.purchase_unit' => ['required', 'string', 'max:20'],
            'items.*.units_per_purchase' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.discount_type' => ['required', 'in:amount,percent'],
            'items.*.discount_value' => ['required', 'numeric', 'min:0'],
        ]);
        $purchase = DB::transaction(function () use ($data, $request): Purchase {
            $preparedItems = collect($data['items'])->map(function (array $item): array {
                $gross = (float) $item['quantity'] * (float) $item['unit_cost'];
                $discount = $item['discount_type'] === 'percent'
                    ? $gross * ((float) $item['discount_value'] / 100)
                    : (float) $item['discount_value'];
                abort_if($discount > $gross, 422, 'Diskon tidak boleh melebihi nilai pembelian.');

                return $item + [
                    'gross' => $gross,
                    'discount' => $discount,
                    'line_total' => $gross - $discount,
                    'stock_quantity' => (int) $item['quantity'] * (int) $item['units_per_purchase'],
                ];
            });
            $total = $preparedItems->sum('line_total');
            $purchase = Purchase::create([
                'purchase_number' => 'PO-'.now()->format('YmdHis').'-'.random_int(10, 99),
                'user_id' => $request->user()->id,
                'supplier_name' => $data['supplier_name'] ?? null,
                'total' => $total,
                'purchased_at' => $data['purchased_at'],
            ]);
            foreach ($preparedItems as $item) {
                $product = Product::where('type', 'product')->lockForUpdate()->findOrFail($item['product_id']);
                $unitCostAfterDiscount = $item['line_total'] / $item['stock_quantity'];
                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'purchase_unit' => $item['purchase_unit'],
                    'units_per_purchase' => $item['units_per_purchase'],
                    'unit_cost' => $item['unit_cost'],
                    'discount_type' => $item['discount_type'],
                    'discount_value' => $item['discount_value'],
                    'stock_quantity' => $item['stock_quantity'],
                    'total' => $item['line_total'],
                ]);
                $product->update(['purchase_price' => $unitCostAfterDiscount, 'stock' => $product->stock + $item['stock_quantity']]);
                StockMovement::create(['product_id' => $product->id, 'user_id' => $request->user()->id, 'type' => 'purchase', 'quantity' => $item['stock_quantity'], 'stock_after' => $product->stock, 'reference' => $purchase->purchase_number]);
            }
            return $purchase;
        });
        app(TelegramService::class)->admin("📦 <b>Stok masuk</b>\nNo. pembelian: {$purchase->purchase_number}\nTotal: Rp ".number_format($purchase->total, 0, ',', '.'));

        return redirect()->route('purchases.create')->with('success', 'Pembelian dan stok berhasil dicatat.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductDeletionRequest;
use App\Models\ProductSaleUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\TelegramService;
use App\Models\Customer;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")))->latest()->paginate(12)->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'deletionRequests' => ProductDeletionRequest::with(['product', 'requestedBy'])->where('status', 'pending')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:product,service'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required_if:type,product', 'integer', 'min:0'],
            'minimum_stock' => ['required_if:type,product', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'wholesale_enabled' => ['nullable', 'boolean'],
            'wholesale_name' => ['required_if:wholesale_enabled,1', 'nullable', 'string', 'max:20'],
            'wholesale_conversion' => ['required_if:wholesale_enabled,1', 'nullable', 'integer', 'min:2'],
            'wholesale_price' => ['required_if:wholesale_enabled,1', 'nullable', 'numeric', 'min:0'],
        ]);
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);
        $wholesaleEnabled = $request->boolean('wholesale_enabled');
        $wholesale = [
            'name' => $data['wholesale_name'] ?? null,
            'conversion' => $data['wholesale_conversion'] ?? null,
            'price' => $data['wholesale_price'] ?? null,
        ];
        unset($data['wholesale_enabled'], $data['wholesale_name'], $data['wholesale_conversion'], $data['wholesale_price']);
        if ($data['type'] === 'service') {
            $data['stock'] = 0;
            $data['minimum_stock'] = 0;
        }
        $product = Product::create($data + ['is_active' => true]);
        ProductSaleUnit::create(['product_id' => $product->id, 'name' => $product->unit, 'conversion' => 1, 'selling_price' => $product->selling_price, 'is_default' => true]);
        if ($wholesaleEnabled) {
            ProductSaleUnit::create([
                'product_id' => $product->id,
                'name' => $wholesale['name'],
                'conversion' => $wholesale['conversion'],
                'selling_price' => $wholesale['price'],
            ]);
        }
        $message = "🆕 <b>Produk baru tersedia</b>\n{$product->name}\nHarga mulai: Rp ".number_format($product->selling_price, 0, ',', '.');
        Customer::where('telegram_opt_in', true)->whereNotNull('telegram_chat_id')->pluck('telegram_chat_id')->each(fn (string $chatId) => app(TelegramService::class)->send($chatId, $message));

        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate(['name' => 'required|string|max:120', 'type' => 'required|in:product,service', 'sku' => 'required|string|max:50|unique:products,sku,'.$product->id, 'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', 'category_id' => 'nullable|exists:categories,id', 'purchase_price' => 'required|numeric|min:0', 'selling_price' => 'required|numeric|min:0', 'stock' => 'required_if:type,product|integer|min:0', 'minimum_stock' => 'required_if:type,product|integer|min:0', 'unit' => 'required|string|max:20']);
        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);
        if ($data['type'] === 'service') {
            $data['stock'] = 0;
            $data['minimum_stock'] = 0;
        }
        $product->update($data);
        if (! $product->saleUnits()->exists()) {
            ProductSaleUnit::create(['product_id' => $product->id, 'name' => $product->unit, 'conversion' => 1, 'selling_price' => $product->selling_price, 'is_default' => true]);
        }

        return back()->with('success', 'Produk diperbarui.');
    }

    public function category(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:80']);
        Category::create(['name' => $data['name'], 'slug' => Str::slug($data['name']).'-'.Str::random(5)]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function requestDeletion(Request $request, Product $product)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        abort_if(ProductDeletionRequest::where('product_id', $product->id)->where('status', 'pending')->exists(), 422, 'Pengajuan penghapusan masih menunggu persetujuan.');
        ProductDeletionRequest::create(['product_id' => $product->id, 'requested_by_user_id' => $request->user()->id, 'reason' => $data['reason']]);

        return back()->with('success', 'Pengajuan penghapusan dikirim ke pimpinan.');
    }

    public function approveDeletion(Request $request, ProductDeletionRequest $deletionRequest)
    {
        abort_if($deletionRequest->status !== 'pending', 422, 'Pengajuan ini sudah diproses.');
        $product = $deletionRequest->product;
        $data = $request->validate(['review_note' => ['nullable', 'string', 'max:500']]);
        $deletionRequest->update(['status' => 'approved', 'reviewed_by_user_id' => $request->user()->id, 'review_note' => $data['review_note'] ?? null, 'reviewed_at' => now()]);
        if ($product->saleItems()->exists() || $product->purchaseItems()->exists()) {
            $product->update(['is_active' => false]);
        } else {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product->delete();
        }

        return back()->with('success', 'Penghapusan disetujui. Riwayat transaksi tetap dipertahankan.');
    }

    public function rejectDeletion(Request $request, ProductDeletionRequest $deletionRequest)
    {
        abort_if($deletionRequest->status !== 'pending', 422, 'Pengajuan ini sudah diproses.');
        $data = $request->validate(['review_note' => ['required', 'string', 'max:500']]);
        $deletionRequest->update(['status' => 'rejected', 'reviewed_by_user_id' => $request->user()->id, 'review_note' => $data['review_note'], 'reviewed_at' => now()]);

        return back()->with('success', 'Pengajuan penghapusan ditolak.');
    }
}

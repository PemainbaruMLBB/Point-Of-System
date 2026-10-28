@extends('layouts.app', ['heading' => 'Pembelian & Stok Masuk'])
@section('content')
<div class="mb-5 rounded-xl border border-blue-500/30 bg-blue-500/10 p-4 text-sm text-blue-200">
    Harga diisi sesuai satuan pembelian. Contoh: 2 box, isi 12 pcs per box, harga Rp 100.000 per box akan menambah stok 24 pcs dengan modal Rp 8.333,33 per pcs.
</div>
<form method="POST" action="{{ route('purchases.store') }}" class="rounded-xl border border-slate-800 bg-slate-900 p-4 sm:p-5" x-data="{items: [{product_id:'',quantity:1,purchase_unit:'pcs',units_per_purchase:1,unit_cost:0,discount_type:'amount',discount_value:0}]}">@csrf
    <div class="grid gap-3 md:grid-cols-2"><input name="supplier_name" placeholder="Nama pemasok (opsional)" class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2"><input name="purchased_at" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}" required class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2"></div>
    <div class="mt-5 space-y-4">
        <template x-for="(item,index) in items" :key="index"><div class="rounded-lg border border-slate-700 p-4">
            <div class="grid gap-3 md:grid-cols-4">
                <select :name="'items['+index+'][product_id]'" required class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2"><option value="">Pilih barang</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>@endforeach</select>
                <input :name="'items['+index+'][quantity]'" type="number" min="1" required placeholder="Jumlah beli" class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2">
                <select :name="'items['+index+'][purchase_unit]'" x-model="item.purchase_unit" class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2"><option value="pcs">Pcs</option><option value="box">Box</option><option value="pack">Pack</option><option value="lusin">Lusin</option><option value="karton">Karton</option></select>
                <input :name="'items['+index+'][units_per_purchase]'" x-model="item.units_per_purchase" type="number" min="1" required placeholder="Isi per satuan" class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2">
            </div>
            <div class="mt-3 grid gap-3 md:grid-cols-4">
                <input :name="'items['+index+'][unit_cost]'" type="number" min="0" step="0.01" required placeholder="Harga per pcs/box" class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2">
                <select :name="'items['+index+'][discount_type]'" x-model="item.discount_type" class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2"><option value="amount">Diskon nominal (Rp)</option><option value="percent">Diskon persen (%)</option></select>
                <input :name="'items['+index+'][discount_value]'" type="number" min="0" step="0.01" value="0" required placeholder="Nilai diskon" class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2">
                <button type="button" @click="items.splice(index,1)" class="rounded-lg border border-red-500/30 px-3 py-2 text-red-300">Hapus baris</button>
            </div>
            <p class="mt-2 text-xs text-slate-500">Stok masuk: jumlah × isi per satuan pembelian.</p>
        </div></template>
    </div>
    <div class="mt-5 flex flex-col gap-3 sm:flex-row"><button type="button" @click="items.push({product_id:'',quantity:1,purchase_unit:'pcs',units_per_purchase:1,unit_cost:0,discount_type:'amount',discount_value:0})" class="rounded-lg border border-slate-700 px-4 py-2">+ Baris barang</button><button class="rounded-lg bg-emerald-400 px-4 py-2 font-bold text-slate-950">Simpan pembelian</button></div>
</form><script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection

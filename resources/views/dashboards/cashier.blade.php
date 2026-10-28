@extends('layouts.app', ['heading' => 'Dashboard Kasir'])
@section('content')
<div class="grid gap-4 md:grid-cols-3">
    <div class="rounded-xl border border-slate-800 bg-slate-900 p-5"><p class="text-sm text-slate-400">Penjualan saya hari ini</p><p class="mt-2 text-3xl font-bold">Rp {{ number_format($mySales, 0, ',', '.') }}</p></div>
    <div class="rounded-xl border border-slate-800 bg-slate-900 p-5"><p class="text-sm text-slate-400">Transaksi saya hari ini</p><p class="mt-2 text-3xl font-bold">{{ $myTransactions }}</p></div>
    <a href="{{ route('sales.create') }}" class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-5"><p class="text-sm text-emerald-300">Mulai bekerja</p><p class="mt-3 font-bold text-emerald-300">Buka kasir baru →</p></a>
</div>
<div class="mt-6 rounded-xl border border-slate-800 bg-slate-900 p-5"><h2 class="mb-4 font-bold">Transaksi saya terakhir</h2><div class="space-y-3">@forelse($recentSales as $sale)<div class="flex justify-between border-b border-slate-800 pb-3"><span>{{ $sale->invoice_number }}<small class="ml-2 text-slate-500">{{ $sale->created_at->format('d M, H:i') }}</small></span><b>Rp {{ number_format($sale->total, 0, ',', '.') }}</b></div>@empty<p class="text-sm text-slate-500">Belum ada transaksi.</p>@endforelse</div></div>
@endsection

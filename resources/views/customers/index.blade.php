@extends('layouts.app', ['heading' => 'Data Pelanggan'])
@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="rounded-xl border border-slate-800 bg-slate-900 p-5"><h2 class="font-bold">Tambah pelanggan</h2><p class="mt-1 text-xs text-slate-400">Diskon otomatis: Member 3%, VIP 5%.</p><form method="POST" action="{{ route('customers.store') }}" class="mt-4 space-y-3">@csrf
        <input name="name" required placeholder="Nama pelanggan" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2">
        <select name="membership_level" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2"><option value="general">Umum - 0%</option><option value="member">Member - 3%</option><option value="vip">VIP - 5%</option></select>
        <input name="phone" placeholder="Nomor telepon/WhatsApp" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2">
        <input name="telegram_chat_id" placeholder="Telegram Chat ID (opsional)" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2">
        <label class="flex items-center gap-2 text-sm"><input name="telegram_opt_in" value="1" type="checkbox" class="rounded"> Izinkan notifikasi Telegram</label>
        <textarea name="address" placeholder="Alamat" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2"></textarea>
        <button class="w-full rounded-lg bg-emerald-400 py-2 font-bold text-slate-950">Simpan pelanggan</button>
    </form></div>
    <div class="overflow-x-auto rounded-xl border border-slate-800 bg-slate-900 lg:col-span-2"><table class="min-w-[700px] w-full text-left text-sm"><thead class="bg-slate-800 text-xs uppercase text-slate-400"><tr><th class="p-4">Pelanggan</th><th class="p-4">Level</th><th class="p-4">Diskon</th><th class="p-4">Telegram</th></tr></thead><tbody>@forelse($customers as $customer)<tr class="border-t border-slate-800"><td class="p-4"><b>{{ $customer->name }}</b><p class="text-xs text-slate-500">{{ $customer->phone ?? '-' }}</p></td><td class="p-4 uppercase">{{ $customer->membership_level }}</td><td class="p-4 text-emerald-300">{{ $customer->discountPercent() }}%</td><td class="p-4">{{ $customer->telegram_opt_in ? 'Aktif' : 'Tidak aktif' }}</td></tr>@empty<tr><td colspan="4" class="p-8 text-center text-slate-500">Belum ada pelanggan.</td></tr>@endforelse</tbody></table></div>
</div><div class="mt-4">{{ $customers->links() }}</div>
@endsection

@extends('layouts.app', ['heading' => 'Pengguna Terdaftar'])
@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="rounded-xl border border-slate-800 bg-slate-900 p-5">
        <h2 class="font-bold">Daftarkan pengguna</h2>
        <p class="mt-1 text-xs text-slate-400">Hanya akun yang dibuat di sini yang dapat masuk.</p>
        <form method="POST" action="{{ route('users.store') }}" class="mt-4 space-y-3">@csrf
            <input name="name" required placeholder="Nama lengkap" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2">
            <input name="email" required type="email" placeholder="Email akun" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2">
            <select name="role" required class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2">@if(auth()->user()->isLeader())<option value="pimpinan">Pimpinan</option><option value="admin">Admin</option>@endif<option value="cashier">Kasir</option></select>
            <input name="password" required type="password" minlength="8" placeholder="Password minimal 8 karakter" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2">
            <input name="password_confirmation" required type="password" placeholder="Ulangi password" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2">
            <button class="w-full rounded-lg bg-emerald-400 py-2 font-bold text-slate-950">Daftarkan akun</button>
        </form>
    </div>
    <div class="overflow-x-auto rounded-xl border border-slate-800 bg-slate-900 lg:col-span-2">
        <table class="w-full text-left text-sm"><thead class="bg-slate-800 text-xs uppercase text-slate-400"><tr><th class="p-4">Pengguna</th><th class="p-4">Peran</th><th class="p-4">Didaftarkan oleh</th><th class="p-4">Status</th><th></th></tr></thead><tbody>@foreach($users as $user)<tr class="border-t border-slate-800"><td class="p-4"><b>{{ $user->name }}</b><p class="text-xs text-slate-500">{{ $user->email }}</p></td><td class="p-4 uppercase">{{ $user->role }}</td><td class="p-4 text-slate-400">{{ $user->creator?->name ?? 'Sistem' }}</td><td class="p-4">{{ $user->activated_at ? 'Aktif' : 'Nonaktif' }}</td><td class="p-4">@if($user->activated_at && !$user->is(auth()->user()))<form method="POST" action="{{ route('users.deactivate', $user) }}">@csrf @method('PATCH')<button class="text-xs text-red-300">Nonaktifkan</button></form>@endif</td></tr>@endforeach</tbody></table>
    </div>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection

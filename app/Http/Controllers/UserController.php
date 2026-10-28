<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        abort_unless(request()->user()?->isLeader(), 403);

        return view('users.index', ['users' => User::with('creator')->latest()->paginate(15)]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->isLeader(), 403);
        $allowedRoles = ['admin', 'cashier', 'pimpinan'];
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in($allowedRoles)],
        ]);
        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'created_by_user_id' => $request->user()->id,
            'activated_at' => now(),
        ]);

        return back()->with('success', 'Pengguna terdaftar dan dapat login.');
    }

    public function deactivate(User $user, Request $request)
    {
        abort_unless($request->user()?->isLeader(), 403);
        abort_if($user->is($request->user()), 422, 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        $user->update(['activated_at' => null]);

        return back()->with('success', 'Akun dinonaktifkan.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\TelegramService;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customers.index', ['customers' => Customer::latest()->paginate(15)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'membership_level' => ['required', Rule::in(['general', 'member', 'vip'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'telegram_chat_id' => ['nullable', 'string', 'max:80'],
            'telegram_opt_in' => ['nullable', 'boolean'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);
        if (empty($data['telegram_chat_id'])) {
            $data['telegram_opt_in'] = false;
        }
        $customer = Customer::create($data);
        if ($customer->telegram_opt_in) {
            app(TelegramService::class)->send($customer->telegram_chat_id, "🎁 <b>Selamat datang, {$customer->name}</b>\nLevel {$customer->membership_level} mendapatkan diskon {$customer->discountPercent()}% pada transaksi.");
        }

        return back()->with('success', 'Pelanggan berhasil ditambahkan.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $today = Carbon::today();
        $user = $request->user();
        $common = [
            'todaySales' => Sale::whereDate('created_at', $today)->sum('total'),
            'todayTransactions' => Sale::whereDate('created_at', $today)->count(),
        ];

        if ($user->role === 'cashier') {
            return view('dashboards.cashier', $common + [
                'mySales' => Sale::where('user_id', $user->id)->whereDate('created_at', $today)->sum('total'),
                'myTransactions' => Sale::where('user_id', $user->id)->whereDate('created_at', $today)->count(),
                'recentSales' => Sale::where('user_id', $user->id)->latest()->limit(8)->get(),
            ]);
        }

        if ($user->role === 'admin') {
            return view('dashboards.admin', $common + [
                'lowStock' => Product::where('type', 'product')->where('is_active', true)->whereColumn('stock', '<=', 'minimum_stock')->orderBy('stock')->limit(8)->get(),
                'recentPurchases' => Purchase::with('user')->latest('purchased_at')->limit(6)->get(),
                'recentSales' => Sale::with('user')->latest()->limit(6)->get(),
            ]);
        }

        return view('dashboards.leader', $common + [
            'monthlySales' => Sale::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total'),
            'monthlyTransactions' => Sale::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'lowStockCount' => Product::where('type', 'product')->where('is_active', true)->whereColumn('stock', '<=', 'minimum_stock')->count(),
            'recentSales' => Sale::with('user')->latest()->limit(8)->get(),
        ]);
    }
}

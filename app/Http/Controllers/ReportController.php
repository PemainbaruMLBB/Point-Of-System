<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();
        $sales = Sale::with(['user', 'customer'])->whereBetween('created_at', [$from, $to])->latest()->paginate(15)->withQueryString();

        $periodSales = Sale::whereBetween('created_at', [$from, $to]);

        return view('reports.sales', ['sales' => $sales, 'from' => $from->toDateString(), 'to' => $to->toDateString(), 'summary' => ['total' => (clone $periodSales)->sum('total'), 'count' => (clone $periodSales)->count(), 'average' => (clone $periodSales)->avg('total') ?? 0, 'taxBase' => (clone $periodSales)->sum('tax_base'), 'finalTax' => (clone $periodSales)->sum('final_tax_amount')], 'products' => Product::whereColumn('stock', '<=', 'minimum_stock')->orderBy('stock')->get()]);
    }
}

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', fn () => redirect()->route(match (auth()->user()->role) {
        'cashier' => 'dashboard.cashier',
        'admin' => 'dashboard.admin',
        'pimpinan' => 'dashboard.leader',
        default => 'login',
    }))->name('dashboard');
    Route::middleware('role:cashier')->get('/dashboard/kasir', DashboardController::class)->name('dashboard.cashier');
    Route::middleware('role:admin')->get('/dashboard/admin', DashboardController::class)->name('dashboard.admin');
    Route::middleware('role:pimpinan')->get('/dashboard/pimpinan', DashboardController::class)->name('dashboard.leader');
    Route::get('/kasir', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/kasir', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/transaksi/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::middleware('role:admin,pimpinan')->patch('/transaksi/{sale}/faktur-pajak', [SaleController::class, 'updateTaxInvoice'])->name('sales.tax-invoice');
    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
        Route::post('/produk', [ProductController::class, 'store'])->name('products.store');
        Route::put('/produk/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::post('/produk/{product}/pengajuan-hapus', [ProductController::class, 'requestDeletion'])->middleware('role:admin')->name('products.deletion-request');
        Route::post('/kategori', [ProductController::class, 'category'])->name('categories.store');
        Route::get('/pembelian', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/pembelian', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/laporan/penjualan', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/pelanggan', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/pelanggan', [CustomerController::class, 'store'])->name('customers.store');
    });
    Route::middleware('role:pimpinan')->group(function () {
        Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
        Route::post('/pengguna', [UserController::class, 'store'])->name('users.store');
        Route::patch('/pengguna/{user}/nonaktifkan', [UserController::class, 'deactivate'])->name('users.deactivate');
    });
    Route::middleware('role:pimpinan')->group(function () {
        Route::post('/pengajuan-hapus-produk/{deletionRequest}/setujui', [ProductController::class, 'approveDeletion'])->name('products.deletion-approve');
        Route::post('/pengajuan-hapus-produk/{deletionRequest}/tolak', [ProductController::class, 'rejectDeletion'])->name('products.deletion-reject');
    });
});

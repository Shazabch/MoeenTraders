<?php


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;

// For enrollment (typically done by an admin from a secure machine, or via a special enrollment kiosk)
Route::post('/enroll-fingerprint', [EmployeeController::class, 'enrollFingerprint']);

// For clock-in/clock-out from the kiosk
Route::post('/clock-action', [AttendanceController::class, 'clockAction']);

// Optional: To check if a fingerprint ID is already taken during enrollment
Route::post('/fingerprint-id-exists', [EmployeeController::class, 'getEmployeeByFingerprintId']);
Route::post('/login', [LoginController::class, 'login'])->name('api.login');

// Route::middleware('auth:sanctum')->controller('DataEntryReportController')->prefix('reports/data-entry')->name('report.data.entry.')->group(function () {
Route::controller('DataEntryReportController')->prefix('reports/data-entry')->name('report.data.entry.')->group(function () {
    Route::get('product', 'product')->name('product');
    Route::get('customer', 'customer')->name('customer');
    Route::get('supplier', 'supplier')->name('supplier');
    Route::get('purchase', 'purchase')->name('purchase');
    Route::get('purchase-return', 'purchaseReturn')->name('purchase.return');
    Route::get('sale', 'sale')->name('sale');
    Route::get('sale-return', 'saleReturn')->name('sale.return');
    Route::get('adjustment', 'adjustment')->name('adjustment');
    Route::get('transfer', 'transfer')->name('transfer');
    Route::get('expense', 'expense')->name('expense');
    Route::get('supplier-payment', 'supplierPayment')->name('supplier.payment');
    Route::get('customer-payment', 'customerPayment')->name('customer.payment');
});
Route::controller(ProductController::class)->group(function () {
    Route::get('all-products', 'getAllProducts')->name('all.products');
    Route::get('all-categories', 'getAllCategories')->name('all.categories');
    Route::get('all-brands', 'getAllBrands')->name('all.brands');
    Route::get('all-units', 'getAllUnits')->name('all.units');
});
Route::controller(OrderController::class)->group(function () {
    Route::post('save-order', 'saveOrder')->name('save-order');
    Route::get('orders', 'getAllOrders')->name('orders');
    Route::post('update-order/{id}', 'updateOrder')->name('update-order');
});

Route::controller(AdminController::class)->group(function () {
    Route::get('dashboard', 'dashboard')->name('dashboard');
});
// web.php or api.php
Route::get('/product/{id}/title', function ($id) {
    return response()->json(['title' => getProductTitle($id)]);
});


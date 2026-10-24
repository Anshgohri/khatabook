<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SalesExcelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('dashboard/notifications/mark-read', [DashboardController::class, 'markAllNotificationsRead'])->name('dashboard.notifications.mark-read');
    Route::post('dashboard/refresh', [DashboardController::class, 'refreshStats'])->name('dashboard.refresh');
    Route::livewire('my-orders', 'pages::my-orders')->name('my-orders');
    Route::livewire('sales', 'pages::sales')->name('sales');
    Route::livewire('sales/create', 'pages::sales-form')->name('sales.create');
    Route::livewire('sales/{sale}/edit', 'pages::sales-form')->name('sales.edit');

    Route::livewire('rentals', 'pages::rentals')->name('rentals');
    Route::livewire('rentals/create', 'pages::rentals-form')->name('rentals.create');
    Route::livewire('rentals/{rental}/edit', 'pages::rentals-form')->name('rentals.edit');

    // Invoice Routes
    Route::get('invoices/sale/{sale}/view', [InvoiceController::class, 'showSaleInvoice'])->name('invoices.sale.view');
    Route::get('invoices/sale/{sale}/download', [InvoiceController::class, 'downloadSaleInvoice'])->name('invoices.sale.download');
    Route::get('invoices/sale/{sale}/print', [InvoiceController::class, 'printSaleInvoice'])->name('invoices.sale.print');

    // Excel Export / Sync Routes
    Route::get('sales/export/excel', [SalesExcelController::class, 'download'])->name('sales.export.excel');

    // Database Backup
    Route::get('admin/database/backup', [DatabaseBackupController::class, 'download'])->name('admin.database.backup');

    Route::livewire('expenses', 'pages::expenses')->name('expenses');
    Route::livewire('expense-categories', 'pages::expense-categories')->name('expense-categories');
    Route::livewire('products', 'pages::products')->name('products');
    Route::livewire('products/create', 'pages::products-form')->name('products.create');
    Route::livewire('products/{product}/edit', 'pages::products-form')->name('products.edit');
    Route::livewire('products/{product}', 'pages::products-detail')->name('products.show');
    Route::livewire('categories', 'pages::categories')->name('categories');
    Route::livewire('employees', 'pages::employees')->name('employees');
    Route::livewire('employees/{employee}', 'pages::employee-ledger')->name('employees.show');
    Route::livewire('employee-rates', 'pages::employee-rates')->name('employee-rates');
    Route::livewire('financiers', 'pages::financiers')->name('financiers');
    Route::livewire('financiers/{financier}', 'pages::financier-ledger')->name('financiers.show');
    Route::livewire('suppliers', 'pages::suppliers')->name('suppliers');
    Route::livewire('suppliers/create', 'pages::suppliers-form')->name('suppliers.create');
    Route::livewire('suppliers/{supplier}/edit', 'pages::suppliers-form')->name('suppliers.edit');
    Route::livewire('suppliers/{supplier}/payments/create', 'pages::supplier-payment-form')->name('suppliers.payments.create');
    Route::livewire('suppliers/{supplier}/payments/{payment}/edit', 'pages::supplier-payment-form')->name('suppliers.payments.edit');
    Route::livewire('suppliers/{supplier}/payments/{payment}', 'pages::supplier-payment-detail')->name('suppliers.payments.show');
    Route::livewire('suppliers/payments/create', 'pages::supplier-payment-form')->name('suppliers.payments.standalone');
    Route::livewire('suppliers/payments/{payment}/edit', 'pages::supplier-payment-form')->name('suppliers.payments.edit.standalone');
    Route::livewire('suppliers/{supplier}', 'pages::supplier-ledger')->name('suppliers.show');
    Route::livewire('production', 'pages::production')->name('production');
    Route::livewire('production/create', 'pages::production-form')->name('production.create');
    Route::livewire('production/{productionLog}/edit', 'pages::production-form')->name('production.edit');
    Route::livewire('users', 'pages::users')->name('users');
    Route::livewire('daily-pnl', 'pages::daily-pnl')->name('daily-pnl');
    Route::livewire('reports', 'pages::reports')->name('reports');
    Route::livewire('product-sales-report', 'pages::product-sales-report')->name('product-sales-report');
    Route::livewire('audit-log', 'pages::audit-log')->name('audit-log');
    Route::livewire('inquiries', 'pages::inquiries')->name('inquiries');
    Route::livewire('store-settings', 'pages::store-settings')->name('store-settings');
});

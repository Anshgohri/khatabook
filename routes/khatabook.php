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
    Route::livewire('categories', 'pages::categories')->name('categories');
    Route::livewire('employees', 'pages::employees')->name('employees');
    Route::livewire('employees/{employee}', 'pages::employee-ledger')->name('employees.show');
    Route::livewire('financiers', 'pages::financiers')->name('financiers');
    Route::livewire('financiers/{financier}', 'pages::financier-ledger')->name('financiers.show');
    Route::livewire('suppliers', 'pages::suppliers')->name('suppliers');
    Route::livewire('suppliers/{supplier}', 'pages::supplier-ledger')->name('suppliers.show');
    Route::livewire('production', 'pages::production')->name('production');
    Route::livewire('production/create', 'pages::production-form')->name('production.create');
    Route::livewire('production/{productionLog}/edit', 'pages::production-form')->name('production.edit');
    Route::livewire('users', 'pages::users')->name('users');
    Route::livewire('daily-pnl', 'pages::daily-pnl')->name('daily-pnl');
    Route::livewire('reports', 'pages::reports')->name('reports');
    Route::livewire('audit-log', 'pages::audit-log')->name('audit-log');
    Route::livewire('inquiries', 'pages::inquiries')->name('inquiries');
    Route::livewire('store-settings', 'pages::store-settings')->name('store-settings');
});

<?php

use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SalesExcelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::khatabook.dashboard')->name('dashboard');
    Route::livewire('my-orders', 'pages::khatabook.my-orders')->name('my-orders');
    Route::livewire('sales', 'pages::khatabook.sales')->name('sales');
    Route::livewire('sales/create', 'pages::khatabook.sales-form')->name('sales.create');
    Route::livewire('sales/{sale}/edit', 'pages::khatabook.sales-form')->name('sales.edit');

    // Invoice Routes
    Route::get('invoices/sale/{sale}/view', [InvoiceController::class, 'showSaleInvoice'])->name('invoices.sale.view');
    Route::get('invoices/sale/{sale}/download', [InvoiceController::class, 'downloadSaleInvoice'])->name('invoices.sale.download');
    Route::get('invoices/sale/{sale}/print', [InvoiceController::class, 'printSaleInvoice'])->name('invoices.sale.print');

    // Excel Export / Sync Routes
    Route::get('sales/export/excel', [SalesExcelController::class, 'download'])->name('sales.export.excel');

    // Database Backup
    Route::get('admin/database/backup', [DatabaseBackupController::class, 'download'])->name('admin.database.backup');

    Route::livewire('expenses', 'pages::khatabook.expenses')->name('expenses');
    Route::livewire('expense-categories', 'pages::khatabook.expense-categories')->name('expense-categories');
    Route::livewire('products', 'pages::khatabook.products')->name('products');
    Route::livewire('categories', 'pages::khatabook.categories')->name('categories');
    Route::livewire('employees', 'pages::khatabook.employees')->name('employees');
    Route::livewire('employees/{employee}', 'pages::khatabook.employee-ledger')->name('employees.show');
    Route::livewire('financiers', 'pages::khatabook.financiers')->name('financiers');
    Route::livewire('financiers/{financier}', 'pages::khatabook.financier-ledger')->name('financiers.show');
    Route::livewire('suppliers', 'pages::khatabook.suppliers')->name('suppliers');
    Route::livewire('suppliers/{supplier}', 'pages::khatabook.supplier-ledger')->name('suppliers.show');
    Route::livewire('production', 'pages::khatabook.production')->name('production');
    Route::livewire('production/create', 'pages::khatabook.production-form')->name('production.create');
    Route::livewire('production/{productionLog}/edit', 'pages::khatabook.production-form')->name('production.edit');
    Route::livewire('users', 'pages::khatabook.users')->name('users');
    Route::livewire('daily-pnl', 'pages::khatabook.daily-pnl')->name('daily-pnl');
    Route::livewire('reports', 'pages::khatabook.reports')->name('reports');
    Route::livewire('audit-log', 'pages::khatabook.audit-log')->name('audit-log');
    Route::livewire('inquiries', 'pages::khatabook.inquiries')->name('inquiries');
    Route::livewire('store-settings', 'pages::khatabook.store-settings')->name('store-settings');
});

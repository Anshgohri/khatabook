<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::khatabook.dashboard')->name('dashboard');
    Route::livewire('sales', 'pages::khatabook.sales')->name('sales');
    Route::livewire('sales/create', 'pages::khatabook.sales-form')->name('sales.create');
    Route::livewire('sales/{sale}/edit', 'pages::khatabook.sales-form')->name('sales.edit');
    Route::livewire('expenses', 'pages::khatabook.expenses')->name('expenses');
    Route::livewire('products', 'pages::khatabook.products')->name('products');
    Route::livewire('categories', 'pages::khatabook.categories')->name('categories');
    Route::livewire('employees', 'pages::khatabook.employees')->name('employees');
    Route::livewire('employees/{employee}', 'pages::khatabook.employee-ledger')->name('employees.show');
    Route::livewire('financiers', 'pages::khatabook.financiers')->name('financiers');
    Route::livewire('financiers/{financier}', 'pages::khatabook.financier-ledger')->name('financiers.show');
    Route::livewire('suppliers', 'pages::khatabook.suppliers')->name('suppliers');
    Route::livewire('suppliers/{supplier}', 'pages::khatabook.supplier-ledger')->name('suppliers.show');
    Route::livewire('production', 'pages::khatabook.production')->name('production');
    Route::livewire('users', 'pages::khatabook.users')->name('users');
    Route::livewire('reports', 'pages::khatabook.reports')->name('reports');
    Route::livewire('audit-log', 'pages::khatabook.audit-log')->name('audit-log');
    Route::livewire('inquiries', 'pages::khatabook.inquiries')->name('inquiries');
});

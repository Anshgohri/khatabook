<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::khatabook.dashboard')->name('dashboard');
    Route::livewire('sales', 'pages::khatabook.sales')->name('sales');
    Route::livewire('expenses', 'pages::khatabook.expenses')->name('expenses');
    Route::livewire('products', 'pages::khatabook.products')->name('products');
    Route::livewire('categories', 'pages::khatabook.categories')->name('categories');
    Route::livewire('users', 'pages::khatabook.users')->name('users');
    Route::livewire('reports', 'pages::khatabook.reports')->name('reports');
    Route::livewire('audit-log', 'pages::khatabook.audit-log')->name('audit-log');
    Route::livewire('inquiries', 'pages::khatabook.inquiries')->name('inquiries');
});

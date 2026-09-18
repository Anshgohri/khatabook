<?php

use App\Http\Controllers\ContactInquiryController;
use App\Http\Controllers\PublicCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicCatalogController::class, 'index'])->name('home');
Route::get('/catalog', [PublicCatalogController::class, 'catalog'])->name('catalog.index');
Route::get('/catalog/{product}', [PublicCatalogController::class, 'show'])->name('catalog.show');

Route::view('/about', 'about')->name('about');
Route::view('/contact', 'contact')->name('contact');
Route::view('/thank-you', 'thank-you')->name('thank-you');
Route::post('/contact', [ContactInquiryController::class, 'store'])->name('contact.store');

Route::livewire('setup', 'pages::auth.setup-admin')->name('setup');

Route::livewire('invite/{user}', 'pages::auth.accept-invite')
    ->middleware('signed')
    ->name('invite.accept');

require __DIR__.'/settings.php';
require __DIR__.'/khatabook.php';

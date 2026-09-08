<?php

use App\Http\Controllers\ContactInquiryController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::redirect('/about', '/#about')->name('about');
Route::redirect('/contact', '/#contact')->name('contact');
Route::view('/thank-you', 'thank-you')->name('thank-you');
Route::post('/contact', [ContactInquiryController::class, 'store'])->name('contact.store');

Route::livewire('setup', 'pages::auth.setup-admin')->name('setup');

Route::livewire('invite/{user}', 'pages::auth.accept-invite')
    ->middleware('signed')
    ->name('invite.accept');

require __DIR__.'/settings.php';
require __DIR__.'/khatabook.php';

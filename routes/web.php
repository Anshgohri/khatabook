<?php

use App\Http\Controllers\ContactInquiryController;
use App\Http\Controllers\PublicCatalogController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

if (app()->environment('local')) {
    Route::get('/test-error/{code}', fn ($code) => abort((int) $code));
}

require __DIR__.'/settings.php';
require __DIR__.'/khatabook.php';

Route::get('/storage/{path}', function (string $path) {
    $normalized = str_replace(['..', "\0"], '', $path);
    $disk = Storage::disk('public');

    if (! $disk->exists($normalized)) {
        abort(404);
    }

    return response()->file($disk->path($normalized));
})->where('path', '.*')->name('storage.local');

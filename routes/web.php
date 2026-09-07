<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::livewire('invite/{user}', 'pages::auth.accept-invite')
    ->middleware('signed')
    ->name('invite.accept');

require __DIR__.'/settings.php';
require __DIR__.'/khatabook.php';

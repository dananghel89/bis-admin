<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'landing.front')->name('front');
Route::view('/terms-of-service', 'landing.tos')->name('tos');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

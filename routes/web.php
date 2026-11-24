<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing.front')->name('front');
Route::view('/terms-of-service', 'landing.terms')->name('tos');
Route::post('/contact', [ContactController::class, 'store'])->name('contact')->middleware('throttle:contact');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::view('/', 'landing.front')->name('front');
Route::view('/tos', 'landing.tos')->name('tos');

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';

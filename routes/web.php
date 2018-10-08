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

/*----------  Landing Routes  ----------*/

Route::get('/', function () {
	return view('landing.front');
})->name('front');

Route::get('/tos', function () {
	return view('landing.tos');
})->name('tos');

/*----------  Authentication Routes  ----------*/

Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

if ( get_option('membership') ) {
	Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
	Route::post('/register', 'Auth\RegisterController@register');
} else {
	Route::get('/register', function () {
		return redirect()->route('front');
	})->name('register');
}

Route::get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('/password/reset', 'Auth\ResetPasswordController@reset');

/*----------  Admin Routes  ----------*/

Route::group(['prefix' => 'dashboard', 'middleware' => 'auth'], function() {
	Route::get('/', 'Admin\DashboardController@index')->name('dashboard');

	Route::get('/settings', 'Admin\SettingsController@index')->name('settings')->middleware('can:editProfile');
	Route::patch('/settings', 'Admin\SettingsController@settingsEdit')->name('settingsEdit')->middleware('can:editProfile');

	Route::get('/options', 'Admin\OptionsController@index')->name('options')->middleware('can:editOptions');
	Route::patch('/options', 'Admin\OptionsController@optionsEdit')->name('optionsEdit')->middleware('can:editOptions');

	Route::get('/users', 'Admin\UserController@index')->name('users')->middleware('can:viewUsers');
	Route::get('/users/add-user', 'Admin\UserController@userAdd')->name('userAdd')->middleware('can:addUsers');
	Route::get('/users/view-user/{id}', 'Admin\UserController@userView')->name('userView')->middleware('can:viewUsers');
	Route::get('/users/edit-user/{id}', 'Admin\UserController@userEdit')->name('userEdit')->middleware('can:editUsers');

	Route::get('/infos', 'Admin\InfoController@index')->name('infos')->middleware('can:viewPosts');
	Route::get('/infos/add-info', 'Admin\InfoController@infoAdd')->name('infoAdd')->middleware('can:addPosts');
	Route::get('/infos/view-info/{id}', 'Admin\InfoController@infoView')->name('infoView')->middleware('can:viewPosts');
	Route::get('/infos/edit-info/{id}', 'Admin\InfoController@infoEdit')->name('infoEdit')->middleware('can:editPosts');
	Route::post('/infos/add-info', 'Admin\InfoController@infoAddSave')->name('infoAddSave')->middleware('can:addPosts');
	Route::patch('/infos/edit-info/{id}', 'Admin\InfoController@infoEditSave')->name('infoEditSave')->middleware('can:editPosts');
	Route::delete('/infos/edit-info/{id}', 'Admin\InfoController@infoDelete')->name('infoDelete')->middleware('can:deletePosts');

	Route::get('/alerts', 'Admin\AlertController@index')->name('alerts')->middleware('can:viewPosts');
	Route::get('/alerts/add-alert', 'Admin\AlertController@alertAdd')->name('alertAdd')->middleware('can:addPosts');
	Route::get('/alerts/view-alert/{id}', 'Admin\AlertController@alertView')->name('alertView')->middleware('can:viewPosts');
	Route::get('/alerts/edit-alert/{id}', 'Admin\AlertController@alertEdit')->name('alertEdit')->middleware('can:editPosts');

	Route::get('/plans', 'Admin\PlanController@index')->name('plans')->middleware('can:viewPosts');
	Route::get('/plans/add-plan', 'Admin\PlanController@planAdd')->name('planAdd')->middleware('can:addPosts');
	Route::get('/plans/view-plan/{id}', 'Admin\PlanController@planView')->name('planView')->middleware('can:viewPosts');
	Route::get('/plans/edit-plan/{id}', 'Admin\PlanController@planEdit')->name('planEdit')->middleware('can:editPosts');

	Route::get('/licenses', 'Admin\LicenseController@index')->name('licenses')->middleware('can:viewPosts');
	Route::get('/licenses/add-license', 'Admin\LicenseController@licenseAdd')->name('licenseAdd')->middleware('can:addPosts');
	Route::get('/licenses/view-license/{id}', 'Admin\LicenseController@licenseView')->name('licenseView')->middleware('can:viewPosts');
	Route::get('/licenses/edit-license/{id}', 'Admin\LicenseController@licenseEdit')->name('licenseEdit')->middleware('can:editPosts');

	Route::get('/messages', 'Admin\EmailController@index')->name('messages')->middleware('can:accessMessages');
	Route::get('/messages/sent', 'Admin\EmailController@messagesSent')->name('messagesSent')->middleware('can:accessMessages');
	Route::get('/messages/send-message', 'Admin\EmailController@messageSend')->name('messageSend')->middleware('can:accessMessages');
	Route::get('/messages/view-message/{id}', 'Admin\EmailController@messageView')->name('messageView')->middleware('can:accessMessages');
});

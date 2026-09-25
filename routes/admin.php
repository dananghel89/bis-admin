<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
*/

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

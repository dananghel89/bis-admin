<?php

use App\Http\Controllers\Admin\AlertController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\InfoController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\OptionsController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings')->middleware('can:editProfile');
    Route::patch('/settings', [SettingsController::class, 'settingsEdit'])->name('settingsEdit')->middleware('can:editProfile');

    Route::get('/options', [OptionsController::class, 'index'])->name('options')->middleware('can:editOptions');
    Route::patch('/options', [OptionsController::class, 'optionsEdit'])->name('optionsEdit')->middleware('can:editOptions');

    Route::get('/users', [UserController::class, 'index'])->name('users')->middleware('can:viewUsers');
    Route::get('/users/add-user', [UserController::class, 'userAdd'])->name('userAdd')->middleware('can:addUsers');
    Route::get('/users/view-user/{id}', [UserController::class, 'userView'])->name('userView')->middleware('can:viewUsers');
    Route::get('/users/edit-user/{id}', [UserController::class, 'userEdit'])->name('userEdit')->middleware('can:editUsers');

    Route::get('/infos', [InfoController::class, 'index'])->name('infos')->middleware('can:viewPosts');
    Route::get('/infos/add-info', [InfoController::class, 'infoAdd'])->name('infoAdd')->middleware('can:addPosts');
    Route::get('/infos/view-info/{id}', [InfoController::class, 'infoView'])->name('infoView')->middleware('can:viewPosts');
    Route::get('/infos/edit-info/{id}', [InfoController::class, 'infoEdit'])->name('infoEdit')->middleware('can:editPosts');
    Route::post('/infos/add-info', [InfoController::class, 'infoAddSave'])->name('infoAddSave')->middleware('can:addPosts');
    Route::patch('/infos/edit-info/{id}', [InfoController::class, 'infoEditSave'])->name('infoEditSave')->middleware('can:editPosts');
    Route::delete('/infos/edit-info/{id}', [InfoController::class, 'infoDelete'])->name('infoDelete')->middleware('can:deletePosts');

    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts')->middleware('can:viewPosts');
    Route::get('/alerts/add-alert', [AlertController::class, 'alertAdd'])->name('alertAdd')->middleware('can:addPosts');
    Route::get('/alerts/view-alert/{id}', [AlertController::class, 'alertView'])->name('alertView')->middleware('can:viewPosts');
    Route::get('/alerts/edit-alert/{id}', [AlertController::class, 'alertEdit'])->name('alertEdit')->middleware('can:editPosts');

    Route::get('/plans', [PlanController::class, 'index'])->name('plans')->middleware('can:viewPosts');
    Route::get('/plans/add-plan', [PlanController::class, 'planAdd'])->name('planAdd')->middleware('can:addPosts');
    Route::get('/plans/view-plan/{id}', [PlanController::class, 'planView'])->name('planView')->middleware('can:viewPosts');
    Route::get('/plans/edit-plan/{id}', [PlanController::class, 'planEdit'])->name('planEdit')->middleware('can:editPosts');

    Route::get('/licenses', [LicenseController::class, 'index'])->name('licenses')->middleware('can:viewPosts');
    Route::get('/licenses/add-license', [LicenseController::class, 'licenseAdd'])->name('licenseAdd')->middleware('can:addPosts');
    Route::get('/licenses/view-license/{id}', [LicenseController::class, 'licenseView'])->name('licenseView')->middleware('can:viewPosts');
    Route::get('/licenses/edit-license/{id}', [LicenseController::class, 'licenseEdit'])->name('licenseEdit')->middleware('can:editPosts');

    Route::get('/messages', [EmailController::class, 'index'])->name('messages')->middleware('can:accessMessages');
    Route::get('/messages/sent', [EmailController::class, 'messagesSent'])->name('messagesSent')->middleware('can:accessMessages');
    Route::get('/messages/send-message', [EmailController::class, 'messageSend'])->name('messageSend')->middleware('can:accessMessages');
    Route::get('/messages/view-message/{id}', [EmailController::class, 'messageView'])->name('messageView')->middleware('can:accessMessages');
});

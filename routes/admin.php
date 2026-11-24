<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
| Everything under /dashboard needs a login. Each route checks its ability
| (AppServiceProvider::defineGates); which properties an architect sees is
| limited in the queries (Property::visibleTo).
*/

Route::prefix('dashboard')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings')->middleware('can:editProfile');
    Route::patch('/settings', [SettingsController::class, 'settingsEdit'])->name('settingsEdit')->middleware('can:editProfile');

    // Properties ("Infos" in the menu): everyone sees the visible ones, staff manage them
    Route::middleware('can:viewProperties')->group(function () {
        Route::get('/infos', [PropertyController::class, 'index'])->name('infos');
        Route::get('/infos/view-info/{property}', [PropertyController::class, 'propertyView'])->name('infoView')->whereNumber('property');
    });
    Route::patch('/infos/work-status/{property}', [PropertyController::class, 'workStatus'])->name('infoWorkStatus')->middleware('can:updateWorkStatus');
    Route::middleware('can:manageProperties')->group(function () {
        Route::get('/infos/add-info', [PropertyController::class, 'propertyAdd'])->name('infoAdd');
        Route::post('/infos/add-info', [PropertyController::class, 'propertyAddSave'])->name('infoAddSave');
        Route::get('/infos/edit-info/{property}', [PropertyController::class, 'propertyEdit'])->name('infoEdit');
        Route::patch('/infos/edit-info/{property}', [PropertyController::class, 'propertyEditSave'])->name('infoEditSave');
        Route::delete('/infos/edit-info/{property}', [PropertyController::class, 'propertyDelete'])->name('infoDelete');
    });

    // Users (staff only)
    Route::middleware('can:manageUsers')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::get('/users/add-user', [UserController::class, 'userAdd'])->name('userAdd');
        Route::post('/users/add-user', [UserController::class, 'userAddSave'])->name('userAddSave');
        Route::get('/users/view-user/{user}', [UserController::class, 'userView'])->name('userView');
        Route::get('/users/edit-user/{user}', [UserController::class, 'userEdit'])->name('userEdit');
        Route::patch('/users/edit-user/{user}', [UserController::class, 'userEditSave'])->name('userEditSave');
        Route::delete('/users/edit-user/{user}', [UserController::class, 'userDelete'])->name('userDelete');
    });

    // Sections still to build: one "coming soon" page each.
    Route::view('/alerts', 'admin.alerts.alertsIndex')->name('alerts')->middleware('can:useMailbox');
    Route::view('/plans', 'admin.plans.plansIndex')->name('plans')->middleware('can:viewProperties');
    Route::view('/licenses', 'admin.licenses.licensesIndex')->name('licenses')->middleware('can:viewProperties');
});

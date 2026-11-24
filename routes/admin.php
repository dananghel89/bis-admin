<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
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

    // Sections still to build: one "coming soon" page each.
    Route::view('/alerts', 'admin.alerts.alertsIndex')->name('alerts')->middleware('can:useMailbox');
    Route::view('/infos', 'admin.infos.infosIndex')->name('infos')->middleware('can:viewProperties');
    Route::view('/plans', 'admin.plans.plansIndex')->name('plans')->middleware('can:viewProperties');
    Route::view('/licenses', 'admin.licenses.licensesIndex')->name('licenses')->middleware('can:viewProperties');
    Route::view('/users', 'admin.users.usersIndex')->name('users')->middleware('can:manageUsers');
});

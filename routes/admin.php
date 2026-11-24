<?php

use App\Http\Controllers\Admin\AlertController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\MailAccountController;
use App\Http\Controllers\Admin\PlanController;
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

    // Your own mail settings for Alerts
    Route::middleware('can:editProfile')->group(function () {
        Route::patch('/settings/mail', [MailAccountController::class, 'save'])->name('settingsMail');
        Route::match(['post', 'patch'], '/settings/mail/test', [MailAccountController::class, 'test'])->name('settingsMailTest');
        Route::delete('/settings/mail', [MailAccountController::class, 'delete'])->name('settingsMailDelete');
    });

    // Alerts: your own mailbox
    Route::middleware('can:useMailbox')->group(function () {
        Route::get('/alerts', [AlertController::class, 'index'])->name('alerts');
        Route::get('/alerts/message/{box}/{uid}', [AlertController::class, 'alertView'])->name('alertView')->whereIn('box', ['inbox', 'sent'])->whereNumber('uid');
        Route::get('/alerts/write', [AlertController::class, 'alertWrite'])->name('alertWrite');
        Route::post('/alerts/write', [AlertController::class, 'alertSend'])->name('alertSend');
    });

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

    // Plans: everyone sees and downloads the visible ones, staff manage them
    Route::middleware('can:viewProperties')->group(function () {
        Route::get('/plans', [PlanController::class, 'index'])->name('plans');
        Route::get('/plans/download/{plan}', [PlanController::class, 'planDownload'])->name('planDownload');
    });
    Route::middleware('can:manageProperties')->group(function () {
        Route::get('/plans/add-plan', [PlanController::class, 'planAdd'])->name('planAdd');
        Route::post('/plans/add-plan', [PlanController::class, 'planAddSave'])->name('planAddSave');
        Route::get('/plans/edit-plan/{plan}', [PlanController::class, 'planEdit'])->name('planEdit');
        Route::patch('/plans/edit-plan/{plan}', [PlanController::class, 'planEditSave'])->name('planEditSave');
        Route::delete('/plans/edit-plan/{plan}', [PlanController::class, 'planDelete'])->name('planDelete');
    });

    // Licenses: everyone sees and downloads the visible ones, staff manage them
    Route::middleware('can:viewProperties')->group(function () {
        Route::get('/licenses', [LicenseController::class, 'index'])->name('licenses');
        Route::get('/licenses/download/{license}', [LicenseController::class, 'licenseDownload'])->name('licenseDownload');
    });
    Route::middleware('can:manageProperties')->group(function () {
        Route::get('/licenses/add-license', [LicenseController::class, 'licenseAdd'])->name('licenseAdd');
        Route::post('/licenses/add-license', [LicenseController::class, 'licenseAddSave'])->name('licenseAddSave');
        Route::get('/licenses/edit-license/{license}', [LicenseController::class, 'licenseEdit'])->name('licenseEdit');
        Route::patch('/licenses/edit-license/{license}', [LicenseController::class, 'licenseEditSave'])->name('licenseEditSave');
        Route::delete('/licenses/edit-license/{license}', [LicenseController::class, 'licenseDelete'])->name('licenseDelete');
    });

    // Contact form messages (staff only)
    Route::middleware('can:readContacts')->group(function () {
        Route::get('/contacts', [ContactController::class, 'index'])->name('contacts');
        Route::get('/contacts/view-contact/{contact}', [ContactController::class, 'contactView'])->name('contactView');
        Route::delete('/contacts/view-contact/{contact}', [ContactController::class, 'contactDelete'])->name('contactDelete');
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
        Route::patch('/users/mail-settings/{user}', [MailAccountController::class, 'save'])->name('userMail');
        Route::match(['post', 'patch'], '/users/mail-settings/{user}/test', [MailAccountController::class, 'test'])->name('userMailTest');
        Route::delete('/users/mail-settings/{user}', [MailAccountController::class, 'delete'])->name('userMailDelete');
    });
});

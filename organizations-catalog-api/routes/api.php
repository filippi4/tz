<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    Route::prefix('organizations')->name('organizations.')->group(function () {
        Route::prefix('geo')->name('geo.')->group(function () {
            Route::get('/radius', [OrganizationController::class, 'geoRadius'])->name('radius');
            Route::get('/bounds', [OrganizationController::class, 'geoBounds'])->name('bounds');
        });
        Route::get('/', [OrganizationController::class, 'index'])->name('index');
        Route::get('/{id}', [OrganizationController::class, 'show'])->name('show');
    });

    Route::prefix('buildings')->name('buildings.')->group(function () {
        Route::get('/', [BuildingController::class, 'index'])->name('index');
        Route::get('/{id}', [BuildingController::class, 'show'])->name('show');
    });

    Route::prefix('activities')->name('activities.')->group(function () {
        Route::get('/', [ActivityController::class, 'index'])->name('index');
        Route::get('/tree', [ActivityController::class, 'tree'])->name('tree');
        Route::get('/{id}', [ActivityController::class, 'show'])->name('show');
    });
});

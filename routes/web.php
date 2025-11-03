<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/redoc', function () {
    return view('vendor.l5-swagger.redoc', ['documentation' => 'default']);
})->name('redoc');

<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| Halaman yang dapat diakses tanpa login (landing page, dll).
| File ini di-include dari routes/web.php.
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

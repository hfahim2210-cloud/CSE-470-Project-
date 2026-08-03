<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GigController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/gigs', [GigController::class, 'index'])->name('gigs.index');
Route::get('/sellers/{user}', [GigController::class, 'sellerProfile'])->name('sellers.profile');
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GigController;

// 1. Redirect root homepage to the Gigs list
Route::get('/', function () {
    return redirect()->route('gigs.index');
});

// 2. Resource route registers ALL CRUD routes: index, create, store, show, edit, update, destroy
Route::resource('gigs', GigController::class);
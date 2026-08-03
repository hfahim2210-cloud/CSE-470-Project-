<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GigController;

// Redirect root homepage to /gigs
Route::get('/', function () {
    return redirect()->route('gigs.index');
});

// Gig Routes
Route::get('/gigs', [GigController::class, 'index'])->name('gigs.index');
Route::get('/gigs/create', [GigController::class, 'create'])->name('gigs.create');
Route::post('/gigs', [GigController::class, 'store'])->name('gigs.store');
<?php

use App\Http\Controllers\GigController;
use App\Http\Controllers\HireRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('gigs.index');
});

Route::resource('gigs', GigController::class);

// Module 3, Feature 1: Submit Hire Request.
Route::get('/gigs/{gig}/hire', [HireRequestController::class, 'create'])
    ->name('hire-requests.create');

Route::post('/gigs/{gig}/hire', [HireRequestController::class, 'store'])
    ->name('hire-requests.store');

// Module 3, Feature 2: View Incoming Hire Requests.
Route::get('/seller/hire-requests', [HireRequestController::class, 'incoming'])
    ->name('hire-requests.incoming');

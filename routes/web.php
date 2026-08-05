<?php

use App\Http\Controllers\DeliverableController;
use App\Http\Controllers\GigController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Redirect the homepage to the seller's gig dashboard.
Route::get('/', function () {
    return redirect()->route('gigs.index');
});

/*
|--------------------------------------------------------------------------
| Marketplace and Seller Profile Routes
|--------------------------------------------------------------------------
|
| Keep /gigs/marketplace before Route::resource('gigs', ...), otherwise
| Laravel may interpret "marketplace" as the {gig} route parameter.
|
*/
Route::get('/gigs/marketplace', [GigController::class, 'marketplace'])
    ->name('gigs.marketplace');

Route::get('/sellers/{user}', [GigController::class, 'sellerProfile'])
    ->name('sellers.profile');

/*
|--------------------------------------------------------------------------
| Gig Archive and Restore Routes
|--------------------------------------------------------------------------
*/
Route::patch('/gigs/{id}/archive', [GigController::class, 'archive'])
    ->name('gigs.archive');

Route::patch('/gigs/{id}/restore', [GigController::class, 'restore'])
    ->name('gigs.restore');

/*
|--------------------------------------------------------------------------
| Temporary Demo Order Route
|--------------------------------------------------------------------------
|
| Remove this route after the Accept Hire Request feature creates real
| orders automatically.
|
*/
Route::post('/gigs/{gig}/demo-order', [OrderController::class, 'createDemo'])
    ->name('orders.demo.store');

/*
|--------------------------------------------------------------------------
| Gig CRUD Routes
|--------------------------------------------------------------------------
*/
Route::resource('gigs', GigController::class);

/*
|--------------------------------------------------------------------------
| Order and Deliverable Routes
|--------------------------------------------------------------------------
*/
Route::get('/orders', [OrderController::class, 'index'])
    ->name('orders.index');

Route::get('/orders/{order}', [OrderController::class, 'show'])
    ->name('orders.show');

// Feature 1: Seller submits the final deliverable.
Route::post('/orders/{order}/deliverable', [DeliverableController::class, 'store'])
    ->name('orders.deliverable.store');

// Feature 2: Buyer approves the final deliverable.
Route::patch('/orders/{order}/deliverable/approve', [DeliverableController::class, 'approve'])
    ->name('orders.deliverable.approve');

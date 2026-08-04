<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GigController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DeliverableController;

// 1. Redirect root homepage to the Gigs list
Route::get('/', function () {
    return redirect()->route('gigs.index');
});

// 2. Resource route registers ALL CRUD routes: index, create, store, show, edit, update, destroy
Route::resource('gigs', GigController::class);

// Module 3: Order delivery and approval routes
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/orders/{order}/deliverable', [DeliverableController::class, 'store'])
    ->name('orders.deliverable.store');
Route::patch('/orders/{order}/deliverable/approve', [DeliverableController::class, 'approve'])
    ->name('orders.deliverable.approve');

// TEMPORARY PLACEHOLDER: remove after Accept Hire Request creates real orders.
Route::post('/gigs/{gig}/demo-order', [OrderController::class, 'createDemo'])
    ->name('orders.demo.store');

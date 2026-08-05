<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GigController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DeliverableController;
use App\Http\Controllers\HireRequestController;

// 1. Redirect root homepage to the Gigs seller management list
Route::get('/', function () {
    return redirect()->route('gigs.index');
});

// 2. Public Marketplace Feed (Browse, Filter, Search - Katha's Module 2)
Route::get('/gigs/marketplace', [GigController::class, 'marketplace'])->name('gigs.marketplace');

// 3. Seller Profile View
Route::get('/sellers/{user}', [GigController::class, 'sellerProfile'])->name('sellers.profile');

// 4. Submit and accept hire requests (Mahi - Module 3)
Route::get('/gigs/{gig}/hire', [HireRequestController::class, 'create'])
    ->name('hire-requests.create');

Route::post('/gigs/{gig}/hire', [HireRequestController::class, 'store'])
    ->name('hire-requests.store');

Route::get('/seller/hire-requests', [HireRequestController::class, 'incoming'])
    ->name('hire-requests.incoming');

Route::patch('/hire-requests/{hireRequest}/accept', [HireRequestController::class, 'accept'])
    ->name('hire-requests.accept');

// 4. Resource route registers ALL Seller CRUD routes (index, create, store, show, edit, update, destroy)
Route::resource('gigs', GigController::class);

Route::patch('/gigs/{id}/archive', [GigController::class, 'archive'])->name('gigs.archive');
Route::patch('/gigs/{id}/restore', [GigController::class, 'restore'])->name('gigs.restore');

// 5. Order pages
Route::get('/orders', [OrderController::class, 'index'])
    ->name('orders.index');

Route::get('/orders/{order}', [OrderController::class, 'show'])
    ->name('orders.show');

// 6. Submit Final Deliverable
Route::post('/orders/{order}/deliverable', [DeliverableController::class, 'store'])
    ->name('orders.deliverable.store');

// 7. Approve Final Deliverable
Route::patch('/orders/{order}/deliverable/approve', [DeliverableController::class, 'approve'])
    ->name('orders.deliverable.approve');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GigController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DeliverableController;
use App\Http\Controllers\HireRequestController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\WishlistController;

// 1. Redirect root homepage to the Gigs seller management list
Route::get('/', function () {
    return redirect()->route('gigs.index');
});

// 2. Public Marketplace Feed (Browse, Filter, Search - Katha's Module 2)
Route::get('/gigs/marketplace', [GigController::class, 'marketplace'])->name('gigs.marketplace');

// 3. Seller Profile View
Route::get('/sellers/{user}', [GigController::class, 'sellerProfile'])->name('sellers.profile');

// 3b. Wishlist (Kotha - Module 2)
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/{gig}', [WishlistController::class, 'store'])->name('wishlist.store');
Route::delete('/wishlist/{gig}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

// 4. Submit and accept hire requests (Mahi - Module 3)
Route::get('/gigs/{gig}/hire', [HireRequestController::class, 'create'])
    ->name('hire-requests.create');

Route::post('/gigs/{gig}/hire', [HireRequestController::class, 'store'])
    ->name('hire-requests.store');

Route::get('/seller/hire-requests', [HireRequestController::class, 'incoming'])
    ->name('hire-requests.incoming');

Route::patch('/hire-requests/{hireRequest}/accept', [HireRequestController::class, 'accept'])
    ->name('hire-requests.accept');

Route::patch('/hire-requests/{hireRequest}/decline', [HireRequestController::class, 'decline'])
    ->name('hire-requests.decline');

// 4. Resource route registers ALL Seller CRUD routes (index, create, store, show, edit, update, destroy)
Route::resource('gigs', GigController::class);

Route::patch('/gigs/{id}/archive', [GigController::class, 'archive'])->name('gigs.archive');
Route::patch('/gigs/{id}/restore', [GigController::class, 'restore'])->name('gigs.restore');

// 5. Order pages
Route::get('/orders', [OrderController::class, 'index'])
    ->name('orders.index');

Route::get('/orders/{order}', [OrderController::class, 'show'])
    ->name('orders.show');

Route::get('/orders/{order}/status', [OrderController::class, 'status'])
    ->name('orders.status');

Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])
    ->name('orders.status.update');

// 6. Submit Final Deliverable
Route::post('/orders/{order}/deliverable', [DeliverableController::class, 'store'])
    ->name('orders.deliverable.store');

// 7. Approve Final Deliverable
Route::patch('/orders/{order}/deliverable/approve', [DeliverableController::class, 'approve'])
    ->name('orders.deliverable.approve');

// 8. Request Revisions
Route::patch('/orders/{order}/deliverable/request-revision', [DeliverableController::class, 'requestRevision'])
    ->name('orders.deliverable.request-revision');

// 9. Leave Text Review
Route::post('/orders/{order}/review', [FeedbackController::class, 'storeReview'])
    ->name('orders.review.store');

// 10. Leave Star Rating
Route::post('/orders/{order}/rating', [FeedbackController::class, 'storeRating'])
    ->name('orders.rating.store');
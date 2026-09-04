<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GigController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DeliverableController;
use App\Http\Controllers\HireRequestController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Guest Routes (Accessible only when NOT logged in)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires valid @g.bracu.ac.bd authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Root Redirect: Logged-in users are routed to their primary dashboard
    Route::get('/', function () {
        return redirect()->route('gigs.index');
    });

    // 1. Marketplace Feed & Seller Profiles
    Route::get('/gigs/marketplace', [GigController::class, 'marketplace'])->name('gigs.marketplace');
    Route::get('/sellers/{user}', [GigController::class, 'sellerProfile'])->name('sellers.profile');

    // 2. Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{gig}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{gig}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // 3. Hire Requests
    Route::get('/gigs/{gig}/hire', [HireRequestController::class, 'create'])->name('hire-requests.create');
    Route::post('/gigs/{gig}/hire', [HireRequestController::class, 'store'])->name('hire-requests.store');
    Route::get('/seller/hire-requests', [HireRequestController::class, 'incoming'])->name('hire-requests.incoming');
    Route::patch('/hire-requests/{hireRequest}/accept', [HireRequestController::class, 'accept'])->name('hire-requests.accept');
    Route::patch('/hire-requests/{hireRequest}/decline', [HireRequestController::class, 'decline'])->name('hire-requests.decline');

    // 4. Seller CRUD Gig Management
    Route::resource('gigs', GigController::class);
    Route::patch('/gigs/{id}/archive', [GigController::class, 'archive'])->name('gigs.archive');
    Route::patch('/gigs/{id}/restore', [GigController::class, 'restore'])->name('gigs.restore');

    // 5. Order Management
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/status', [OrderController::class, 'status'])->name('orders.status');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');

    // 6. Deliverables & Revisions
    Route::post('/orders/{order}/deliverable', [DeliverableController::class, 'store'])->name('orders.deliverable.store');
    Route::patch('/orders/{order}/deliverable/approve', [DeliverableController::class, 'approve'])->name('orders.deliverable.approve');
    Route::patch('/orders/{order}/deliverable/request-revision', [DeliverableController::class, 'requestRevision'])->name('orders.deliverable.request-revision');

    // 7. Reviews & Ratings
    Route::post('/orders/{order}/review', [FeedbackController::class, 'storeReview'])->name('orders.review.store');
    Route::post('/orders/{order}/rating', [FeedbackController::class, 'storeRating'])->name('orders.rating.store');
});
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeliverableController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GigController;
use App\Http\Controllers\HireRequestController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Public pages: guests may browse the marketplace, gigs and seller profiles.
Route::redirect('/', '/gigs/marketplace');
Route::get('/gigs/marketplace', [GigController::class, 'marketplace'])->name('gigs.marketplace');
Route::get('/sellers/{user}', [GigController::class, 'sellerProfile'])->name('sellers.profile');
Route::get('/media', [MediaController::class, 'show'])->name('media.show');

// Seller-only gig management and fulfilment actions.
Route::middleware(['auth', 'role:seller'])->group(function (): void {
    Route::get('/gigs', [GigController::class, 'index'])->name('gigs.index');
    Route::get('/gigs/create', [GigController::class, 'create'])->name('gigs.create');
    Route::post('/gigs', [GigController::class, 'store'])->name('gigs.store');
    Route::get('/gigs/{gig}/edit', [GigController::class, 'edit'])->name('gigs.edit');
    Route::put('/gigs/{gig}', [GigController::class, 'update'])->name('gigs.update');
    Route::delete('/gigs/{gig}', [GigController::class, 'destroy'])->name('gigs.destroy');
    Route::patch('/gigs/{id}/archive', [GigController::class, 'archive'])->name('gigs.archive');
    Route::patch('/gigs/{id}/restore', [GigController::class, 'restore'])->name('gigs.restore');

    Route::get('/seller/hire-requests', [HireRequestController::class, 'incoming'])
        ->name('hire-requests.incoming');
    Route::patch('/hire-requests/{hireRequest}/accept', [HireRequestController::class, 'accept'])
        ->name('hire-requests.accept');
    Route::patch('/hire-requests/{hireRequest}/decline', [HireRequestController::class, 'decline'])
        ->name('hire-requests.decline');

    Route::get('/orders/{order}/status', [OrderController::class, 'status'])
        ->name('orders.status');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->name('orders.status.update');
    Route::post('/orders/{order}/deliverable', [DeliverableController::class, 'store'])
        ->name('orders.deliverable.store');
});

// Buyer-only hiring, wishlist and feedback actions.
Route::middleware(['auth', 'role:buyer'])->group(function (): void {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{gig}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{gig}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    Route::get('/gigs/{gig}/hire', [HireRequestController::class, 'create'])
        ->name('hire-requests.create');
    Route::post('/gigs/{gig}/hire', [HireRequestController::class, 'store'])
        ->name('hire-requests.store');

    Route::patch('/orders/{order}/deliverable/approve', [DeliverableController::class, 'approve'])
        ->name('orders.deliverable.approve');
    Route::patch('/orders/{order}/deliverable/request-revision', [DeliverableController::class, 'requestRevision'])
        ->name('orders.deliverable.request-revision');
    Route::post('/orders/{order}/review', [FeedbackController::class, 'storeReview'])
        ->name('orders.review.store');
    Route::post('/orders/{order}/rating', [FeedbackController::class, 'storeRating'])
        ->name('orders.rating.store');
});

// Both buyers and sellers may see only orders in which they participate.
Route::middleware(['auth', 'role:buyer,seller'])->group(function (): void {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Keep this dynamic public route after /gigs/create and other specific gig routes.
Route::get('/gigs/{gig}', [GigController::class, 'show'])->name('gigs.show');

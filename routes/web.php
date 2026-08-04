<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GigController;

// 1. Redirect root homepage to the Gigs seller management list
Route::get('/', function () {
    return redirect()->route('gigs.index');
});

// 2. Public Marketplace Feed (Browse, Filter, Search - Katha's Module 2)
Route::get('/gigs/marketplace', [GigController::class, 'marketplace'])->name('gigs.marketplace');

// 3. Seller Profile View
Route::get('/sellers/{user}', [GigController::class, 'sellerProfile'])->name('sellers.profile');

// 4. Resource route registers ALL Seller CRUD routes (index, create, store, show, edit, update, destroy)
Route::resource('gigs', GigController::class);
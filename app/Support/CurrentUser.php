<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class CurrentUser
{
    /**
     * TEMPORARY PLACEHOLDER FOR THE UNFINISHED AUTHENTICATION MODULE.
     * Replace this method with auth()->user() after team integration.
     */
    public static function buyer(): User
    {
        return User::query()->firstOrCreate(
            ['email' => 'buyer@example.com'],
            [
                'name' => 'Temporary Buyer',
                'password' => Hash::make('password'),
            ]
        );
    }

    /**
     * TEMPORARY PLACEHOLDER FOR THE UNFINISHED AUTHENTICATION MODULE.
     * The current Gig module also falls back to seller user ID 1.
     */
    public static function seller(): User
    {
        return User::query()->firstOrCreate(
            ['email' => 'seller@example.com'],
            [
                'name' => 'Temporary Seller',
                'password' => Hash::make('password'),
            ]
        );
    }
}

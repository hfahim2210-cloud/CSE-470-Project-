<?php

namespace App\Support;

use App\Models\Gig;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class CurrentUser
{
    /**
     * Temporary authentication fallback for Module 3 development.
     * Real authentication automatically takes priority when it is available.
     */
    public static function buyerFor(Gig $gig): User
    {
        if (Auth::check() && Auth::id() !== $gig->user_id) {
            return Auth::user();
        }

        return User::query()->firstOrCreate(
            ['email' => 'buyer@example.com'],
            [
                'name' => 'Temporary Buyer',
                'password' => 'temporary-password',
            ]
        );
    }

    /**
     * Uses the authenticated seller when available; otherwise follows the
     * existing project convention of using the seeded seller with ID 1.
     */
    public static function seller(): User
    {
        if (Auth::check()) {
            return Auth::user();
        }

        return User::query()->findOrFail(1);
    }
}

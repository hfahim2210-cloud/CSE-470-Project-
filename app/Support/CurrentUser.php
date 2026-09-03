<?php

namespace App\Support;

use App\Models\Gig;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class CurrentUser
{
    public static function buyerFor(Gig $gig): User
    {
        abort_unless(
            Auth::check() && Auth::user()->role === 'buyer',
            403,
            'Only a buyer account can submit a hire request.'
        );

        return Auth::user();
    }

    public static function seller(): User
    {
        abort_unless(
            Auth::check() && Auth::user()->role === 'seller',
            403,
            'Only a seller account can perform this action.'
        );

        return Auth::user();
    }
}

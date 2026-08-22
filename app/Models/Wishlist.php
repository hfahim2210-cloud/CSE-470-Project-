<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $fillable = [
        'user_id',
        'gig_id',
    ];

    // the buyer who saved this gig
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // the gig that was saved
    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }
}
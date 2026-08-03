<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gig extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'category',
        'is_active',
    ];

    /**
     * Get the seller who owns this gig.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
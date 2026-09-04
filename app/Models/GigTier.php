<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GigTier extends Model
{
    protected $fillable = [
        'gig_id', 'tier_type', 'title', 'description', 'price', 'delivery_days', 'revisions'
    ];

    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }
}
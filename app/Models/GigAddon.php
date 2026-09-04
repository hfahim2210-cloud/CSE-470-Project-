<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GigAddon extends Model
{
    protected $fillable = ['gig_id', 'title', 'price', 'extra_delivery_days'];

    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }
}
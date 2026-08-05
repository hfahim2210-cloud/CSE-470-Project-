<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gig extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'category',
        'delivery_time',
        'image',
        'status',
        //'is_archived',
    ];

    //seller who owns this gig
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //portfolio items attached to dis gig
    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class);
    }
}
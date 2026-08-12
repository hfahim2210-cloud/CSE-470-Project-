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
        'max_weekly_orders',
        'is_accepting_orders',
        //'is_archived',
    ];

    // Seller who owns this gig
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Portfolio items attached to this gig
    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class);
    }

    /**
     * Get all orders created for this gig.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Helper: Calculate active orders created in the current week.
     */
    public function activeOrdersCount(): int
    {
        return $this->orders()
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();
    }

    /**
     * Helper: Check if gig is available for hiring.
     */
    public function isAvailable(): bool
    {
        if (!$this->is_accepting_orders) {
            return false;
        }

        return $this->activeOrdersCount() < $this->max_weekly_orders;
    }
}
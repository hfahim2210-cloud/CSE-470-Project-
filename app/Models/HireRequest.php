<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HireRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';

    protected $fillable = [
        'gig_id',
        'buyer_id',
        'seller_id',
        'message',
        'proposed_deadline',
        'selected_tier',
        'selected_addons',
        'quoted_price',
        'status',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'proposed_deadline' => 'date',
            'selected_tier' => 'array',
            'selected_addons' => 'array',
            'quoted_price' => 'decimal:2',
            'accepted_at' => 'datetime',
        ];
    }

    public function gig(): BelongsTo
    {
        return $this->belongsTo(Gig::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}

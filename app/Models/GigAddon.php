<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GigAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'gig_id',
        'name',
        'description',
        'price',
        'extra_days',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'extra_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function gig(): BelongsTo
    {
        return $this->belongsTo(Gig::class);
    }
}

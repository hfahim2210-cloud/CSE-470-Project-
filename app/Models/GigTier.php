<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GigTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'gig_id',
        'name',
        'title',
        'description',
        'price',
        'delivery_time',
        'revisions',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'delivery_time' => 'integer',
            'revisions' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function gig(): BelongsTo
    {
        return $this->belongsTo(Gig::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'gig_id',
        'title',
        'file_path',
        'file_type',
    ];

    /**
     * Get the gig this portfolio item belongs to.
     */
    public function gig(): BelongsTo
    {
        return $this->belongsTo(Gig::class);
    }
}
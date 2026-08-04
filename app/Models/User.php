<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Optional: if you store seller/buyer roles
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all gigs created by the seller.
     */
    public function gigs(): HasMany
    {
        return $this->hasMany(Gig::class);
    }

    /**
     * Hire requests submitted by this user as a buyer.
     */
    public function submittedHireRequests(): HasMany
    {
        return $this->hasMany(HireRequest::class, 'buyer_id');
    }

    /**
     * Hire requests received by this user as a seller.
     */
    public function receivedHireRequests(): HasMany
    {
        return $this->hasMany(HireRequest::class, 'seller_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'avatar',
        'provider',
        'provider_id',
        'publish_credits',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'publish_credits'   => 'integer',
        ];
    }

    /** Suma créditos de publicación (al confirmarse un pago). */
    public function addCredits(int $amount): void
    {
        $this->increment('publish_credits', $amount);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}

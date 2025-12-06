<?php

namespace App\Models;

use App\Utils\Traits\CustomHasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use CustomHasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'password',
        'credits',
        'phone',
        'database'
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}

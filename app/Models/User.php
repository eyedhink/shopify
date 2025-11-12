<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
    ];
}

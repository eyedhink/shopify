<?php

namespace App\Models;

use App\Utils\Traits\CustomHasApiTokens;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

class Admin extends Model
{
    use CustomHasApiTokens;

    protected $fillable = [
        'name',
        'password',
        'is_main_admin',
        'abilities',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_main_admin' => 'boolean',
        'abilities' => 'array',
    ];

    protected $hidden = [
        'password',
    ];
}

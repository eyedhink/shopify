<?php

namespace App\Models;

use App\Utils\Traits\CustomHasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}

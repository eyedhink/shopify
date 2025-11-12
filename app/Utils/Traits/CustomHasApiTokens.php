<?php

namespace App\Utils\Traits;

use DateTimeInterface;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

trait CustomHasApiTokens
{
    use HasApiTokens {
        HasApiTokens::createToken as parentCreateToken;
    }

    public function createToken(string $name, string $db, array $abilities = ['*'], ?DateTimeInterface $expiresAt = null): NewAccessToken
    {
        $token = $this->parentCreateToken($name, $abilities, $expiresAt);
        $token->plainTextToken = hash('sha256', $db) . "|" . explode("|", $token->plainTextToken)[1];
        return $token;
    }
}

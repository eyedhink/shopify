<?php

namespace App\Utils\Traits;

use App\Utils\Exceptions\AccessDeniedException;
use DateTimeInterface;
use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use PDO;

trait CustomHasApiTokens
{
    use HasApiTokens {
        HasApiTokens::createToken as parentCreateToken;
    }

    /**
     * @throws EnvException
     * @throws AccessDeniedException
     */
    public function createToken(string $name, Request $request, array $abilities = ['*'], ?DateTimeInterface $expiresAt = null): NewAccessToken
    {
        $editor = new EnvEditor(new Repository(), new Filesystem());
        $token = $this->parentCreateToken($name, $abilities, $expiresAt);
        $pdo = new PDO($editor->getKey('DBS_DB_DSN'));
        $stmt = $pdo->query("SELECT name FROM `databases`");
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $name) {
            $editor->editKey("DB_DATABASE", $name);
            $tokenModel = $token->accessToken;
            if ($tokenModel !== null) {
//                $model = $tokenModel['tokenable']::query()->find(($tokenModel['tokenable_id']));
                $model = $tokenModel->tokenable;
                if ($model !== null) {
                    $db = $model['database'];
                    $token->plainTextToken = hash('sha256', $db) . "|" . explode("|", $token->plainTextToken)[1];
                    return $token;
                }
            }
        }
        throw new AccessDeniedException();
    }
}

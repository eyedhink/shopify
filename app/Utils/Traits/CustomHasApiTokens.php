<?php

namespace App\Utils\Traits;

use DateTimeInterface;
use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use PDO;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

trait CustomHasApiTokens
{
    use HasApiTokens {
        HasApiTokens::createToken as parentCreateToken;
    }

    /**
     * @throws EnvException
     */
    public function createToken(string $name, Request $request, array $abilities = ['*'], ?DateTimeInterface $expiresAt = null): NewAccessToken
    {
        $token = $this->parentCreateToken($name, $abilities, $expiresAt);
        $pdo = new PDO("sqlite:D:\programming\PHP\shopify\database\db.sqlite");
        $stmt = $pdo->query("SELECT name FROM `databases`");
        $editor = new EnvEditor(new Repository(), new Filesystem());
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $name) {
            $editor->editKey("DB_DATABASE", $name);
            $tokenModel = PersonalAccessToken::query()->find(explode("|", $token->plainTextToken)[0]);
            if ($tokenModel !== null) {
                $model = $tokenModel['tokenable']::query()->find(($tokenModel['tokenable_id']));
                if ($model !== null) {
                    $db = $model['database'];
                    $token->plainTextToken = hash('sha256', $db) . "|" . explode("|", $token->plainTextToken)[1];
                    return $token;
                }
            }
        }
        throw new UnauthorizedHttpException("Unauthorized");
    }
}

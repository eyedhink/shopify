<?php

namespace App\Utils\Models;

use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use PDO;

class CustomPersonalAccessToken extends PersonalAccessToken
{
    protected $table = 'personal_access_tokens';

    /**
     * @throws EnvException
     */
    public static function customFindToken($token, Request $request)
    {
        $editor = new EnvEditor(new Repository(), new Filesystem());
        $db = explode("|", explode("Bearer ", $request->header('Authorization'))[1])[0];
        $pdo = new PDO($editor->getKey('DBS_DB_DSN'));
        $stmt = $pdo->query("SELECT name FROM `databases`");
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $name) {
            if (hash('sha256', $name) == $db) {
                $editor->editKey('DB_DATABASE', $name);
            }
        }
        $ex_token = explode('|', $token);
        $access_token = $ex_token[1];
        $token = PersonalAccessToken::query()->where('token', hash('sha256', $access_token))->first();
        return parent::findToken($token->id . "|" . $access_token)->token;
    }

    public static function findToken($token)
    {
        return static::query()->where('token', $token)->first();
    }
}


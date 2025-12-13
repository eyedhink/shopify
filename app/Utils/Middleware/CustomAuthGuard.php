<?php

namespace App\Utils\Middleware;

use Closure;
use GeoSot\EnvEditor\EnvEditor;
use GeoSot\EnvEditor\Exceptions\EnvException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use PDO;

class CustomAuthGuard extends Authenticate
{
    /**
     * @throws AuthenticationException
     * @throws EnvException
     */
    public function handle($request, Closure $next, ...$guards)
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
        $ex_token = explode('|', explode("Bearer ", $request->header('Authorization'))[1]);
        $access_token = $ex_token[1];
        $request->headers->set('Authorization', "Bearer " . $access_token);
        return parent::handle($request, $next, ...$guards);
    }
}

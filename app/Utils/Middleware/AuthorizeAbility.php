<?php

namespace App\Utils\Middleware;

use App\Utils\Exceptions\AccessDeniedException;
use App\Utils\Functions\FunctionUtils;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeAbility
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     * @throws AccessDeniedException
     */
    public function handle(Request $request, Closure $next, string $ability): Response
    {

        if (!FunctionUtils::isAuthorized($request->user('admin'), $ability)) {
            throw new AccessDeniedException();
        }

        return $next($request);
    }
}

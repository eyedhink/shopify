<?php

namespace App\Utils\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class AccessDeniedException extends Exception
{
    public function render(): Response
    {
        $message = $this->getMessage() ?: "دسترسی مجاز نیست.";

        return response()->json([
            'status' => 'error',
            'code' => Response::HTTP_FORBIDDEN,
            'message' => $message,
            'reason' => 'PermissionDenied',
        ], Response::HTTP_FORBIDDEN);
    }
}

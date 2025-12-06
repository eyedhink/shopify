<?php

namespace App\Utils\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ImpossibleRequestException extends Exception
{
    public function render(): Response
    {
        $message = $this->getMessage() ?: "انجام این عملیات ممکن نیست.";

        return response()->json([
            'status' => 'error',
            'code' => Response::HTTP_METHOD_NOT_ALLOWED,
            'message' => $message,
            'reason' => 'PermissionDenied',
        ], Response::HTTP_METHOD_NOT_ALLOWED);
    }
}

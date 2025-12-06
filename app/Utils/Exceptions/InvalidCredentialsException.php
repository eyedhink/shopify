<?php

namespace App\Utils\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class InvalidCredentialsException extends Exception
{
    public function render(): Response
    {
        $message = $this->getMessage() ?: "اطلاعات وارد شده درست نیست.";

        return response()->json([
            'status' => 'error',
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => $message,
            'reason' => 'PermissionDenied',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}

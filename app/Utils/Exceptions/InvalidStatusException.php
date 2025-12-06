<?php

namespace App\Utils\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class InvalidStatusException extends Exception
{
    public function render(): Response
    {
        $message = $this->getMessage() ?: "وضعیت وارد شده درست نیست.";

        return response()->json([
            'status' => 'error',
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => $message,
            'reason' => 'PermissionDenied',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}

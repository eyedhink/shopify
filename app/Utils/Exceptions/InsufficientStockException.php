<?php

namespace App\Utils\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class InsufficientStockException extends Exception
{
    public function render(): Response
    {
        $message = $this->getMessage() ?: "تعداد کافی از کالای مورد نظر موجود نیست.";

        return response()->json([
            'status' => 'error',
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => $message,
            'reason' => 'PermissionDenied',
        ], Response::HTTP_BAD_REQUEST);
    }
}

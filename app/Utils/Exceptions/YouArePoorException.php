<?php

namespace App\Utils\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class YouArePoorException extends Exception
{
    public function render(): Response
    {
        $message = $this->getMessage() ?: "موجودی شما کافی نیست.";

        return response()->json([
            'status' => 'error',
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => $message,
            'reason' => 'PermissionDenied',
        ], Response::HTTP_BAD_REQUEST);
    }
}

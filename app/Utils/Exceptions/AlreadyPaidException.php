<?php

namespace App\Utils\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class AlreadyPaidException extends Exception
{
    public function render(): Response
    {
        $message = $this->getMessage() ?: "هزینه پرداخت شده است.";

        return response()->json([
            'status' => 'error',
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => $message,
            'reason' => 'PermissionDenied',
        ], Response::HTTP_BAD_REQUEST);
    }
}

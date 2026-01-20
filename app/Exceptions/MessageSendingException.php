<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class MessageSendingException extends Exception
{
    public function render()
    {
        return response()->json(
            [
                'error' => $this->message,
                'code' => $this->code ?? 500,
            ],
            $this->code ?? 500,
        );
    }
}

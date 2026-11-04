<?php

namespace App\Services;

use RuntimeException;

class BucketApiException extends RuntimeException
{
    public function __construct(string $message, public readonly ?int $statusCode = null)
    {
        parent::__construct($message);
    }
}

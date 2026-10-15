<?php

namespace ParselPilot;

use RuntimeException;

final class ApiException extends RuntimeException
{
    public function __construct(
        private readonly int $statusCode
    ) {
        parent::__construct(
            "The parcel API returned HTTP status {$statusCode}.",
            $statusCode
        );
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}

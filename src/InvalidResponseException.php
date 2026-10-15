<?php

namespace ParselPilot;

use RuntimeException;

final class InvalidResponseException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The parcel API returned a non-JSON or unexpected response.');
    }
}

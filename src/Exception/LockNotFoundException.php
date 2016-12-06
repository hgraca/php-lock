<?php

namespace Hgraca\Lock\Exception;

use Exception;

class LockNotFoundException extends Exception
{
    public function __construct(string $message = 'There is no lock on this process!', int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

<?php
namespace Hgraca\Lock\Exception;

use Exception;

class CouldNotCreateLockException extends Exception
{
    public function __construct(string $message = 'Could not create the lock!', int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

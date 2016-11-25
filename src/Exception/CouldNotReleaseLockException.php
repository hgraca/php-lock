<?php
namespace Hgraca\Lock\Exception;

use Exception;

class CouldNotReleaseLockException extends Exception
{
    public function __construct(string $message = 'Could not release the lock!', int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

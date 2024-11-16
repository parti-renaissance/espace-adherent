<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class MaxLoginAttemptException extends UserNotFoundException
{
    public function getMessageKey(): string
    {
        return 'Max attempts reached.';
    }
}

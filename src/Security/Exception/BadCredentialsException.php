<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class BadCredentialsException extends UserNotFoundException
{
    public function getMessageKey(): string
    {
        return 'Invalid credentials.';
    }
}

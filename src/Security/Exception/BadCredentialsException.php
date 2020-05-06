<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class BadCredentialsException extends UsernameNotFoundException
{
    public function getMessageKey()
    {
        return 'Invalid credentials.';
    }
}

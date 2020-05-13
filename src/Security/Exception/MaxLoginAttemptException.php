<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class MaxLoginAttemptException extends UsernameNotFoundException
{
    public function getMessageKey()
    {
        return 'Max attempts reached.';
    }
}

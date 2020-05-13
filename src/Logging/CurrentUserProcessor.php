<?php

namespace App\Logging;

use Symfony\Component\Security\Core\Security;

class CurrentUserProcessor
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function processRecord(array $record): array
    {
        $user = $this->security->getUser();

        $record['extra']['user'] = $user ? $user->getUsername() : 'anonymous';

        return $record;
    }
}

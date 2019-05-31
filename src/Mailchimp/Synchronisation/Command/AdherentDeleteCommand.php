<?php

namespace AppBundle\Mailchimp\Synchronisation\Command;

use AppBundle\Mailchimp\SynchronizeMessageInterface;

class AdherentDeleteCommand implements SynchronizeMessageInterface
{
    private $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}

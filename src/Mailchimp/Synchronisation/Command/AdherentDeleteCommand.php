<?php

namespace AppBundle\Mailchimp\Synchronisation\Command;

use AppBundle\Messenger\Message\AsyncMessageInterface;

class AdherentDeleteCommand implements AsyncMessageInterface
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

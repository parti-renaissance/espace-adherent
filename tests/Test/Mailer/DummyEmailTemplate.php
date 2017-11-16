<?php

namespace Tests\AppBundle\Test\Mailer;

use AppBundle\Mailer\EmailTemplate;

class DummyEmailTemplate extends EmailTemplate
{
    public function addRecipient(string $email, string $name = null, array $vars = [])
    {
        $this->recipients[] = [
            'email' => $email,
            'name' => $name,
            'vars' => $vars,
        ];
    }

    public function getBody(): array
    {
        return [];
    }
}

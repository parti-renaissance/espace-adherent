<?php

namespace Tests\App\Test\Mailer;

use App\Mailer\AbstractEmailTemplate;

class DummyEmailTemplate extends AbstractEmailTemplate
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

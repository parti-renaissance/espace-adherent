<?php

declare(strict_types=1);

namespace App\Mailer;

class Sender
{
    private ?string $name;
    private ?string $email;

    public function __construct(?string $name = null, ?string $email = null)
    {
        $this->name = $name;
        $this->email = $email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}

<?php

namespace App\Adhesion;

use App\Validator\StrictEmail;
use Symfony\Component\Validator\Constraints as Assert;

class EmailValidationRequest
{
    /**
     * @Assert\NotBlank
     * @StrictEmail
     */
    private ?string $email = null;

    public ?string $token = null;

    public function setEmail(?string $email): void
    {
        $this->email = strtolower($email);
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}

<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Command;

class JemarcheDataSurveyCreateCommand implements JemarcheDataSurveyCommandInterface
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

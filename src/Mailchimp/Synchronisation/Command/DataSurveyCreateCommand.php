<?php

namespace App\Mailchimp\Synchronisation\Command;

class DataSurveyCreateCommand implements DataSurveyCommandInterface
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

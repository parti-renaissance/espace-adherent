<?php

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\SynchronizeMessageInterface;

interface JemarcheDataSurveyCommandInterface extends SynchronizeMessageInterface
{
    public function getEmail(): string;
}

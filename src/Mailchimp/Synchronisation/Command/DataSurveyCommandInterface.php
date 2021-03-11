<?php

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\SynchronizeMessageInterface;

interface DataSurveyCommandInterface extends SynchronizeMessageInterface
{
    public function getEmail(): string;
}

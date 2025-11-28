<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\SynchronizeMessageInterface;

interface JemarcheDataSurveyCommandInterface extends SynchronizeMessageInterface
{
    public function getEmail(): string;
}

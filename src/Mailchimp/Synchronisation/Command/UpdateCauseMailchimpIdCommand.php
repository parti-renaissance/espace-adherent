<?php

namespace App\Mailchimp\Synchronisation\Command;

use App\Entity\Coalition\Cause;

class UpdateCauseMailchimpIdCommand
{
    private $cause;

    public function __construct(Cause $cause)
    {
        $this->cause = $cause;
    }

    public function getCause(): Cause
    {
        return $this->cause;
    }
}

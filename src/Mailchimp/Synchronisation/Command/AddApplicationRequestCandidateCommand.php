<?php

namespace AppBundle\Mailchimp\Synchronisation\Command;

use AppBundle\Mailchimp\SynchronizeMessageInterface;

class AddApplicationRequestCandidateCommand implements SynchronizeMessageInterface
{
    private $applicationRequestId;
    private $type;

    public function __construct(int $applicationRequestId, string $type)
    {
        $this->applicationRequestId = $applicationRequestId;
        $this->type = $type;
    }

    public function getApplicationRequestId(): int
    {
        return $this->applicationRequestId;
    }

    public function getType(): string
    {
        return $this->type;
    }
}

<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\Synchronisation\QueuePriorityLevelEnum;
use Jwage\PhpAmqpLibMessengerBundle\Transport\AmqpStamp;
use Symfony\Component\Messenger\Message\DefaultStampsProviderInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Component\Uid\Uuid;

class AdherentChangeCommand implements AdherentChangeCommandInterface, DefaultStampsProviderInterface
{
    private $uuid;
    private $emailAddress;
    private $removedTags;

    public function __construct(
        Uuid $uuid,
        string $emailAddress,
        array $removedTags = [],
        private readonly bool $batch = false,
    ) {
        $this->uuid = $uuid;
        $this->emailAddress = strtolower($emailAddress);
        $this->removedTags = $removedTags;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getRemovedTags(): array
    {
        return $this->removedTags;
    }

    public function getDefaultStamps(): array
    {
        if ($this->batch) {
            return [
                new TransportNamesStamp(QueuePriorityLevelEnum::QUEUE_NAME),
                AmqpStamp::createWithAttributes(['priority' => QueuePriorityLevelEnum::LOW]),
            ];
        }

        return [];
    }
}

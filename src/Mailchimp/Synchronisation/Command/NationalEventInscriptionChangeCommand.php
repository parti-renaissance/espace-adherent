<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\Synchronisation\QueuePriorityLevelEnum;
use App\Mailchimp\SynchronizeMessageInterface;
use Jwage\PhpAmqpLibMessengerBundle\Transport\AmqpStamp;
use Symfony\Component\Messenger\Message\DefaultStampsProviderInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Component\Uid\Uuid;

class NationalEventInscriptionChangeCommand implements SynchronizeMessageInterface, DefaultStampsProviderInterface
{
    public function __construct(
        public readonly Uuid $uuid,
        public readonly ?string $oldEmailAddress = null,
        private readonly bool $batch = false,
    ) {
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

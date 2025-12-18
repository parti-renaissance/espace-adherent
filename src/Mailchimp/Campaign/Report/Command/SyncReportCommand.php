<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Report\Command;

use App\Mailchimp\CampaignMessageInterface;
use App\Mailchimp\Synchronisation\QueuePriorityLevelEnum;
use App\Messenger\Message\AbstractUuidMessage;
use Jwage\PhpAmqpLibMessengerBundle\Transport\AmqpStamp;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Message\DefaultStampsProviderInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class SyncReportCommand extends AbstractUuidMessage implements CampaignMessageInterface, DefaultStampsProviderInterface
{
    public function __construct(
        UuidInterface $uuid,
        public readonly bool $firstRun = false,
        public readonly bool $autoReschedule = true,
        public int $step = 1,
        public readonly bool $lowPriority = false,
        public readonly ?int $delay = null,
    ) {
        parent::__construct($uuid);
    }

    public function getDefaultStamps(): array
    {
        $stamps = [
            new TransportNamesStamp(QueuePriorityLevelEnum::QUEUE_NAME),
            AmqpStamp::createWithAttributes(['priority' => $this->lowPriority ? QueuePriorityLevelEnum::MEDIUM : QueuePriorityLevelEnum::HIGH]),
        ];

        if ($this->delay > 0) {
            $stamps[] = new DelayStamp($this->delay);
        }

        return $stamps;
    }
}

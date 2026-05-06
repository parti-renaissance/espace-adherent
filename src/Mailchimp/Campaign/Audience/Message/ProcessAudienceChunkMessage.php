<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience\Message;

use App\Mailchimp\Synchronisation\QueuePriorityLevelEnum;
use Jwage\PhpAmqpLibMessengerBundle\Transport\AmqpStamp;
use Symfony\Component\Messenger\Message\DefaultStampsProviderInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class ProcessAudienceChunkMessage implements DefaultStampsProviderInterface
{
    public function __construct(
        public int $mailchimpCampaignId,
        public int $chunkNumber,
    ) {
    }

    public function getDefaultStamps(): array
    {
        return [
            new TransportNamesStamp(QueuePriorityLevelEnum::QUEUE_NAME),
            AmqpStamp::createWithAttributes(['priority' => QueuePriorityLevelEnum::HIGH]),
        ];
    }
}

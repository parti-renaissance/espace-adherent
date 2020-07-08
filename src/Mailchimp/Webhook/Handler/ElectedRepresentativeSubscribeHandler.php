<?php

namespace App\Mailchimp\Webhook\Handler;

use App\ElectedRepresentative\ElectedRepresentativeSubscriptionHandler;
use App\Mailchimp\Webhook\EventTypeEnum;

class ElectedRepresentativeSubscribeHandler extends AbstractElectedRepresentativeHandler
{
    private $subscriptionHandler;

    public function __construct(ElectedRepresentativeSubscriptionHandler $subscriptionHandler)
    {
        $this->subscriptionHandler = $subscriptionHandler;
    }

    public function handle(array $data): void
    {
        foreach ($this->findElectedRepresentatives($data['email']) as $electedRepresentative) {
            $this->subscriptionHandler->subscribe($electedRepresentative);
        }
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::SUBSCRIBE === $type && parent::support($type, $listId);
    }
}

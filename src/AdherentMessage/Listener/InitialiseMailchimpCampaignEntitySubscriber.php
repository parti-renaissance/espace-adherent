<?php

declare(strict_types=1);

namespace App\AdherentMessage\Listener;

use App\AdherentMessage\Events;
use App\AdherentMessage\MailchimpCampaign\Handler\MailchimpCampaignHandlerInterface;
use App\AdherentMessage\MessageEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitialiseMailchimpCampaignEntitySubscriber implements EventSubscriberInterface
{
    /** @var MailchimpCampaignHandlerInterface[]|iterable */
    private $handlers;

    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::MESSAGE_PRE_CREATE => ['initialiseMailchimpCampaign'],
            Events::MESSAGE_FILTER_PRE_EDIT => ['initialiseMailchimpCampaign'],
        ];
    }

    public function initialiseMailchimpCampaign(MessageEvent $event): void
    {
        $message = $event->getMessage();

        if ($message->isSent() || $message->isStatutory()) {
            return;
        }

        foreach ($this->handlers as $handler) {
            if ($handler->supports($message)) {
                $handler->handle($message);

                return;
            }
        }
    }
}

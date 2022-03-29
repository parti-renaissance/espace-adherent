<?php

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

    public static function getSubscribedEvents()
    {
        return [
            Events::MESSAGE_PRE_CREATE => ['initialiseMailchimpCampaign'],
            Events::MESSAGE_FILTER_PRE_EDIT => ['initialiseMailchimpCampaign'],
        ];
    }

    public function initialiseMailchimpCampaign(MessageEvent $event): void
    {
        $message = $event->getMessage();

        if ($message->isSent()) {
            return;
        }

        foreach ($this->getHandlers() as $handler) {
            if ($handler->supports($message)) {
                $handler->handle($message);

                return;
            }
        }
    }

    /**
     * @return MailchimpCampaignHandlerInterface[]
     */
    private function getHandlers(): array
    {
        $handlers = iterator_to_array($this->handlers);

        usort($handlers, function (MailchimpCampaignHandlerInterface $handlerA, MailchimpCampaignHandlerInterface $handlerB) {
            return $handlerB->getPriority() <=> $handlerA->getPriority();
        });

        return $handlers;
    }
}

<?php

namespace AppBundle\AdherentMessage\Listener;

use AppBundle\AdherentMessage\Events;
use AppBundle\AdherentMessage\MailchimpCampaign\Handler\MailchimpCampaignHandlerInterface;
use AppBundle\AdherentMessage\MessageEvent;
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
            Events::MESSAGE_PRE_CREATE => ['initialiseMailchimpCampaign', 10000],
            Events::MESSAGE_FILTER_PRE_EDIT => ['initialiseMailchimpCampaign', 10000],
        ];
    }

    public function initialiseMailchimpCampaign(MessageEvent $event): void
    {
        $message = $event->getMessage();

        foreach ($this->handlers as $handler) {
            if ($handler->supports($message)) {
                $handler->handle($message);

                return;
            }
        }
    }
}

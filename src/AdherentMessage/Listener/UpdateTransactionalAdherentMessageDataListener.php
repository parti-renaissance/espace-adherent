<?php

namespace App\AdherentMessage\Listener;

use App\AdherentMessage\Events;
use App\AdherentMessage\MessageEvent;
use App\AdherentMessage\TransactionalMessage\MessageModifier\MessageModifierInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateTransactionalAdherentMessageDataListener implements EventSubscriberInterface
{
    /** @var MessageModifierInterface[]|iterable */
    private $modifiers;

    public function __construct(iterable $modifiers)
    {
        $this->modifiers = $modifiers;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::MESSAGE_FILTER_PRE_EDIT => 'onFilterPreEdit',
        ];
    }

    public function onFilterPreEdit(MessageEvent $event): void
    {
        $message = $event->getMessage();

        foreach ($this->modifiers as $modifier) {
            if ($modifier->support($message)) {
                $modifier->modify($message);
            }
        }
    }
}

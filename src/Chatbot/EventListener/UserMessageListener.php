<?php

namespace App\Chatbot\EventListener;

use App\Chatbot\Assistant;
use App\Chatbot\Event\UserMessageEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class UserMessageListener
{
    public function __construct(private readonly Assistant $assistant)
    {
    }

    public function __invoke(UserMessageEvent $event)
    {
        $this->assistant->handle($event->message);
    }
}

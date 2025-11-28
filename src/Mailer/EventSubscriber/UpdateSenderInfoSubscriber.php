<?php

declare(strict_types=1);

namespace App\Mailer\EventSubscriber;

use App\Mailer\Event\MailerEvent;
use App\Mailer\Event\MailerEvents;
use App\Mailer\SenderMessageMapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateSenderInfoSubscriber implements EventSubscriberInterface
{
    private SenderMessageMapper $mapper;

    public function __construct(SenderMessageMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MailerEvents::BEFORE_EMAIL_BUILD => 'updateSenderInfo',
        ];
    }

    public function updateSenderInfo(MailerEvent $event): void
    {
        $message = $event->getMessage();

        if (!$sender = $this->mapper->findForMessage($message)) {
            return;
        }

        if (!$message->getSenderEmail()) {
            $message->setSenderEmail($sender->getEmail());
        }

        if (!$message->getSenderName()) {
            $message->setSenderName($sender->getName());
        }
    }
}

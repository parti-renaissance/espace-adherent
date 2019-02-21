<?php

namespace AppBundle\InstitutionalEvent;

use AppBundle\Entity\InstitutionalEvent;
use AppBundle\Events;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\InstitutionalEventInvitationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InstitutionalEventMessageNotifier implements EventSubscriberInterface
{
    private $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function onInstitutionalEventCreated(InstitutionalEventEvent $institutionalEvent): void
    {
        $this->mailer->sendMessage($this->createMessage($institutionalEvent->getInstitutionalEvent()));
    }

    public function onInstitutionalEventUpdated(InstitutionalEventEvent $institutionalEvent): void
    {
        $this->mailer->sendMessage($this->createMessage($institutionalEvent->getInstitutionalEvent()));
    }

    private function createMessage(InstitutionalEvent $institutionalEvent): InstitutionalEventInvitationMessage
    {
        return InstitutionalEventInvitationMessage::createFromInstitutionalEvent($institutionalEvent);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::INSTITUTIONAL_EVENT_CREATED => ['onInstitutionalEventCreated', -128],
            Events::INSTITUTIONAL_EVENT_UPDATED => ['onInstitutionalEventUpdated', -128],
        ];
    }
}

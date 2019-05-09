<?php

namespace AppBundle\Assessor;

use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\AssessorRequestAssociateMessage;
use AppBundle\Mailer\Message\AssessorRequestConfirmationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class AssessorRequestNotifier implements EventSubscriberInterface
{
    private $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function onRequestSent(Event $event)
    {
        /** @var AssessorRequestCommand $command */
        if (!$command = $event->getSubject()) {
            return;
        }

        $this->mailer->sendMessage(
            AssessorRequestConfirmationMessage::createFromAssessorRequestCommand($command)
        );
    }

    public function onRequestAssociated(AssessorRequestEvent $event)
    {
        $this->mailer->sendMessage(
           AssessorRequestAssociateMessage::createFromAssessorRequest($event->getAssessorRequest())
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            AssessorRequestEnum::REQUEST_SENT => 'onRequestSent',
            AssessorRequestEnum::REQUEST_ASSOCIATED => 'onRequestAssociated',
        ];
    }
}

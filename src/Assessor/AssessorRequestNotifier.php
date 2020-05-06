<?php

namespace App\Assessor;

use App\Mailer\MailerService;
use App\Mailer\Message\AssessorRequestAssociateMessage;
use App\Mailer\Message\AssessorRequestConfirmationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Workflow\Event\Event;

class AssessorRequestNotifier implements EventSubscriberInterface
{
    private $mailer;
    private $translator;

    public function __construct(MailerService $mailer, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
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
           AssessorRequestAssociateMessage::create(
               $event->getAssessorRequest(),
               $this->translator->trans($event->getAssessorRequest()->getOfficeName())
           )
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

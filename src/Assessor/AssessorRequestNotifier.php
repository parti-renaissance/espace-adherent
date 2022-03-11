<?php

namespace App\Assessor;

use App\Mailer\MailerService;
use App\Mailer\Message\Assessor\AssessorRequestAssociateMessage;
use App\Mailer\Message\Assessor\AssessorRequestConfirmationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class AssessorRequestNotifier implements EventSubscriberInterface
{
    private $mailer;
    private $translator;

    public function __construct(MailerService $transactionalMailer, TranslatorInterface $translator)
    {
        $this->mailer = $transactionalMailer;
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

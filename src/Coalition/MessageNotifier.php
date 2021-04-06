<?php

namespace App\Coalition;

use App\Entity\Coalition\Cause;
use App\Entity\Coalition\CauseFollower;
use App\Mailer\MailerService;
use App\Mailer\Message\Coalition\CauseCreationConfirmationMessage;
use App\Mailer\Message\Coalition\CauseFollowerAnonymousConfirmationMessage;
use App\Mailer\Message\Coalition\CauseFollowerConfirmationMessage;

class MessageNotifier
{
    private $mailer;
    private $coalitionUrlGenerator;

    public function __construct(MailerService $transactionalMailer, CoalitionUrlGenerator $coalitionUrlGenerator)
    {
        $this->mailer = $transactionalMailer;
        $this->coalitionUrlGenerator = $coalitionUrlGenerator;
    }

    public function sendCauseCreationConfirmationMessage(Cause $cause): void
    {
        $causeListLink = $this->coalitionUrlGenerator->generateCauseListLink();

        $this->mailer->sendMessage(CauseCreationConfirmationMessage::create($cause, $causeListLink));
    }

    public function sendCauseFollowerConfirmationMessage(CauseFollower $causeFollower): void
    {
        $causeLink = $this->coalitionUrlGenerator->generateCauseLink($causeFollower->getCause());

        $this->mailer->sendMessage(CauseFollowerConfirmationMessage::create($causeFollower, $causeLink));
    }

    public function sendCauseFollowerAnonymousConfirmationMessage(CauseFollower $causeFollower): void
    {
        $causeLink = $this->coalitionUrlGenerator->generateCauseLink($causeFollower->getCause());
        $createAccountLink = $this->coalitionUrlGenerator->generateCreateAccountLink();

        $this->mailer->sendMessage(CauseFollowerAnonymousConfirmationMessage::create(
            $causeFollower,
            $causeLink,
            $createAccountLink
        ));
    }
}

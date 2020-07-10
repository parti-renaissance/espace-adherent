<?php

namespace App\VotingPlatform\Notifier;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Election;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeElectionVoteIsOpenMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ElectionNotifier
{
    private $mailer;
    private $urlGenerator;

    public function __construct(MailerService $transactionalMailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
    }

    public function notifyCommitteeElectionVoteIsOpen(
        Adherent $adherent,
        Election $election,
        Committee $committee
    ): void {
        $this->mailer->sendMessage(CommitteeElectionVoteIsOpenMessage::create(
            $adherent,
            $committee,
            $election->getDesignation(),
            $this->urlGenerator->generate('app_committee_show', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }
}

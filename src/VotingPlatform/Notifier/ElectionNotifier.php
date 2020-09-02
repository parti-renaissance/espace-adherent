<?php

namespace App\VotingPlatform\Notifier;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeElectionCandidacyPeriodIsOverMessage;
use App\Mailer\Message\CommitteeElectionSecondRoundNotificationMessage;
use App\Mailer\Message\CommitteeElectionVoteIsOverMessage;
use App\Mailer\Message\CommitteeElectionVoteReminderMessage;
use App\Mailer\Message\VotingPlatformElectionVoteIsOpenMessage;
use App\VotingPlatform\Designation\DesignationTypeEnum;
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

    public function notifyElectionVoteIsOpen(Election $election, array $adherents): void
    {
        if (DesignationTypeEnum::COPOL === $election->getDesignationType()) {
            $url = $this->urlGenerator->generate('app_territorial_council_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            $url = $this->urlGenerator->generate('app_committee_show', ['slug' => $election->getElectionEntity()->getCommittee()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $this->mailer->sendMessage(VotingPlatformElectionVoteIsOpenMessage::create($election, $adherents, $url));
    }

    public function notifyCommitteeElectionCandidacyPeriodIsOver(
        Adherent $adherent,
        Designation $designation,
        Committee $committee
    ): void {
        $this->mailer->sendMessage(CommitteeElectionCandidacyPeriodIsOverMessage::create(
            $adherent,
            $committee,
            $designation,
            $this->urlGenerator->generate('app_committee_show', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

    public function notifyCommitteeElectionVoteReminder(
        Adherent $adherent,
        Designation $designation,
        Committee $committee
    ): void {
        $this->mailer->sendMessage(CommitteeElectionVoteReminderMessage::create(
            $adherent,
            $committee,
            $designation,
            $this->urlGenerator->generate('app_committee_show', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

    public function notifyCommitteeElectionVoteIsOver(Adherent $adherent, Committee $committee): void
    {
        $this->mailer->sendMessage(CommitteeElectionVoteIsOverMessage::create(
            $adherent,
            $committee,
            $this->urlGenerator->generate('app_committee_show', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

    public function notifyCommitteeElectionSecondRound(
        Adherent $adherent,
        Election $election,
        Committee $committee
    ): void {
        $this->mailer->sendMessage(CommitteeElectionSecondRoundNotificationMessage::create(
            $adherent,
            $election,
            $committee,
            $this->urlGenerator->generate('app_committee_show', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }
}

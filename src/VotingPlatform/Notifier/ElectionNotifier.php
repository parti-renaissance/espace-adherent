<?php

namespace App\VotingPlatform\Notifier;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Voter;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeElectionCandidacyPeriodIsOverMessage;
use App\Mailer\Message\CommitteeElectionVoteReminderMessage;
use App\Mailer\Message\VotingPlatformElectionSecondRoundNotificationMessage;
use App\Mailer\Message\VotingPlatformElectionVoteIsOpenMessage;
use App\Mailer\Message\VotingPlatformElectionVoteIsOverMessage;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ElectionNotifier
{
    private $mailer;
    private $urlGenerator;
    private $voterRepository;

    public function __construct(
        MailerService $transactionalMailer,
        UrlGeneratorInterface $urlGenerator,
        VoterRepository $voterRepository
    ) {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
        $this->voterRepository = $voterRepository;
    }

    public function notifyElectionVoteIsOpen(Election $election): void
    {
        $adherents = $this->getAdherentForElection($election);

        if ($adherents) {
            $this->mailer->sendMessage(VotingPlatformElectionVoteIsOpenMessage::create(
                $election,
                $adherents,
                $this->getUrl($election)
            ));
        }
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

    public function notifyElectionVoteIsOver(Election $election): void
    {
        $adherents = $this->getAdherentForElection($election);

        if ($adherents) {
            $this->mailer->sendMessage(VotingPlatformElectionVoteIsOverMessage::create(
                $election,
                $adherents,
                $this->getUrl($election)
            ));
        }
    }

    public function notifyElectionSecondRound(Election $election): void
    {
        $adherents = $this->getAdherentForElection($election);

        if ($adherents) {
            $this->mailer->sendMessage(VotingPlatformElectionSecondRoundNotificationMessage::create(
                $election,
                $adherents,
                $this->getUrl($election)
            ));
        }
    }

    /**
     * @return Adherent[]
     */
    private function getAdherentForElection(Election $election): array
    {
        $voters = $this->voterRepository->findForElection($election, true);

        return array_map(function (Voter $voter) { return $voter->getAdherent(); }, $voters);
    }

    private function getUrl(Election $election): string
    {
        if (DesignationTypeEnum::COPOL === $election->getDesignationType()) {
            return $this->urlGenerator->generate('app_territorial_council_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType()) {
            return $this->urlGenerator->generate('app_adherent_profile_activity', ['_fragment' => 'committees'], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->urlGenerator->generate('app_committee_show', ['slug' => $election->getElectionEntity()->getCommittee()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

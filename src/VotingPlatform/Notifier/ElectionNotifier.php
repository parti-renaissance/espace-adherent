<?php

namespace App\VotingPlatform\Notifier;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Voter;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeElectionCandidacyPeriodIsOverMessage;
use App\Mailer\Message\VotingPlatformElectionSecondRoundNotificationMessage;
use App\Mailer\Message\VotingPlatformElectionVoteIsOpenMessage;
use App\Mailer\Message\VotingPlatformElectionVoteIsOverMessage;
use App\Mailer\Message\VotingPlatformVoteReminderMessage;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\VotingPlatform\VoteRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ElectionNotifier
{
    private $mailer;
    private $urlGenerator;
    private $voterRepository;
    private $committeeMembershipRepository;
    private $voteRepository;

    public function __construct(
        MailerService $transactionalMailer,
        UrlGeneratorInterface $urlGenerator,
        VoterRepository $voterRepository,
        VoteRepository $voteRepository,
        CommitteeMembershipRepository $committeeMembershipRepository
    ) {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
        $this->voterRepository = $voterRepository;
        $this->voteRepository = $voteRepository;
        $this->committeeMembershipRepository = $committeeMembershipRepository;
    }

    public function notifyElectionVoteIsOpen(Election $election): void
    {
        if (!$election->getDesignation()->isNotificationVoteOpenedEnabled()) {
            return;
        }

        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType()) {
            $committeeMemberships = $this->committeeMembershipRepository->findVotingForElectionMemberships(
                $election->getElectionEntity()->getCommittee(),
                $election->getDesignation(),
                false
            );

            $adherents = array_map(function (CommitteeMembership $membership) { return $membership->getAdherent(); }, $committeeMemberships);
        } else {
            $adherents = $this->getAdherentForElection($election);
        }

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

    public function notifyVotingPlatformVoteReminder(Election $election, Adherent $adherent): void
    {
        if (!$election->getDesignation()->isNotificationVoteReminderEnabled()) {
            return;
        }

        $this->mailer->sendMessage(VotingPlatformVoteReminderMessage::create(
            $election,
            $adherent,
            $this->getUrl($election)
        ));
    }

    public function notifyElectionVoteIsOver(Election $election): void
    {
        if (!$election->getDesignation()->isNotificationVoteClosedEnabled()) {
            return;
        }

        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType()) {
            $adherents = array_map(
                function (Voter $voter) { return $voter->getAdherent(); },
                $this->voterRepository->findVotedForElection($election)
            );
        } else {
            $adherents = $this->getAdherentForElection($election);
        }

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
        if (!$election->getDesignation()->isNotificationSecondRoundEnabled()) {
            return;
        }

        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType()) {
            $committeeMemberships = $this->committeeMembershipRepository->findVotingForElectionMemberships(
                $election->getElectionEntity()->getCommittee(),
                $election->getDesignation(),
                false
            );

            $adherents = array_map(function (CommitteeMembership $membership) { return $membership->getAdherent(); }, array_filter($committeeMemberships, function (CommitteeMembership $membership) use ($election) {
                $votes = $this->voteRepository->findVoteForDesignation($membership->getAdherent(), $election->getDesignation());

                foreach ($votes as $vote) {
                    if ($vote->getElection()->getId() !== $election->getId()) {
                        return false;
                    }
                }

                return true;
            }));
        } else {
            $adherents = $this->getAdherentForElection($election);
        }

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
        if ($election->getDesignation()->isCopolType()) {
            return $this->urlGenerator->generate('app_territorial_council_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType()) {
            if ($election->isClosed()) {
                return $this->urlGenerator->generate('app_committee_show', ['slug' => $election->getElectionEntity()->getCommittee()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
            }

            return $this->urlGenerator->generate('app_adherent_profile_activity', ['_fragment' => 'committees'], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->urlGenerator->generate('app_committee_show', ['slug' => $election->getElectionEntity()->getCommittee()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

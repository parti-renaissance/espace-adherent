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
use App\Mailer\Message\Renaissance\VotingPlatform\VotingPlatformLocalElectionVoteIsOpenMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\VotingPlatformLocalElectionVoteIsOverMessage;
use App\Mailer\Message\VotingPlatformElectionSecondRoundNotificationMessage;
use App\Mailer\Message\VotingPlatformElectionVoteIsOpenMessage;
use App\Mailer\Message\VotingPlatformElectionVoteIsOverMessage;
use App\Mailer\Message\VotingPlatformVoteReminderMessage;
use App\Mailer\Message\VotingPlatformVoteStatusesIsOpenMessage;
use App\Mailer\Message\VotingPlatformVoteStatusesIsOverMessage;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\VotingPlatform\VoteRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ElectionNotifier
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly VoterRepository $voterRepository,
        private readonly VoteRepository $voteRepository,
        private readonly CommitteeMembershipRepository $committeeMembershipRepository
    ) {
    }

    public function notifyElectionVoteIsOpen(Election $election): void
    {
        if (
            !$election->getDesignation()->isNotificationVoteOpenedEnabled()
            || $election->isNotificationAlreadySent(Designation::NOTIFICATION_VOTE_OPENED)
        ) {
            return;
        }

        if (($electionType = $election->getDesignationType()) === DesignationTypeEnum::COMMITTEE_SUPERVISOR) {
            $committeeMemberships = $this->committeeMembershipRepository->findVotingForElectionMemberships(
                $election->getElectionEntity()->getCommittee(),
                $election->getDesignation(),
                false
            );

            $getRecipientsCallback = function () use ($committeeMemberships): array {
                return array_map(function (CommitteeMembership $membership) { return $membership->getAdherent(); }, $committeeMemberships);
            };
        } else {
            $getRecipientsCallback = function (int $offset, int $limit) use ($election): array {
                return $this->getAdherentForElection($election, $offset, $limit);
            };
        }

        $url = $this->getUrl($election);

        $this->batchSendEmail(
            $getRecipientsCallback,
            function (array $recipients) use ($election, $electionType, $url) {
                if (DesignationTypeEnum::POLL === $electionType) {
                    return VotingPlatformVoteStatusesIsOpenMessage::create($election, $recipients, $url);
                }

                if ($election->getDesignation()->isLocalElectionTypes()) {
                    return VotingPlatformLocalElectionVoteIsOpenMessage::create($election, $recipients, $url);
                }

                return VotingPlatformElectionVoteIsOpenMessage::create($election, $recipients, $url);
            },
            DesignationTypeEnum::COMMITTEE_SUPERVISOR !== $electionType
        );

        $election->markSentNotification(Designation::NOTIFICATION_VOTE_OPENED);
        $this->entityManager->flush();
    }

    public function notifyCommitteeElectionCandidacyPeriodIsOver(
        Adherent $adherent,
        Designation $designation,
        Committee $committee
    ): void {
        $this->transactionalMailer->sendMessage(CommitteeElectionCandidacyPeriodIsOverMessage::create(
            $adherent,
            $committee,
            $designation,
            $this->urlGenerator->generate('app_committee_show', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

    public function notifyVotingPlatformVoteReminder(Election $election, Adherent $adherent): void
    {
        if (
            !$election->getDesignation()->isNotificationVoteReminderEnabled()
            || $election->isNotificationAlreadySent(Designation::NOTIFICATION_VOTE_REMINDER)
        ) {
            return;
        }

        $this->transactionalMailer->sendMessage(VotingPlatformVoteReminderMessage::create(
            $election,
            $adherent,
            $this->getUrl($election)
        ));

        $election->markSentNotification(Designation::NOTIFICATION_VOTE_REMINDER);
        $this->entityManager->flush();
    }

    public function notifyElectionVoteIsOver(Election $election): void
    {
        if (
            !$election->getDesignation()->isNotificationVoteClosedEnabled()
            || $election->isNotificationAlreadySent(Designation::NOTIFICATION_VOTE_CLOSED)
        ) {
            return;
        }

        if (($electionType = $election->getDesignationType()) === DesignationTypeEnum::COMMITTEE_SUPERVISOR) {
            $getRecipientsCallback = function () use ($election): array {
                return array_map(
                    function (Voter $voter) { return $voter->getAdherent(); },
                    $this->voterRepository->findVotedForElection($election)
                );
            };
        } else {
            $getRecipientsCallback = function (int $offset, int $limit) use ($election): array {
                return $this->getAdherentForElection($election, $offset, $limit);
            };
        }

        $url = $this->getUrl($election);

        $this->batchSendEmail(
            $getRecipientsCallback,
            function (array $recipients) use ($election, $electionType, $url) {
                if (DesignationTypeEnum::POLL === $electionType) {
                    return VotingPlatformVoteStatusesIsOverMessage::create($election, $recipients, $url);
                }

                if ($election->getDesignation()->isLocalElectionTypes()) {
                    return VotingPlatformLocalElectionVoteIsOverMessage::create($election, $recipients, $url);
                }

                return VotingPlatformElectionVoteIsOverMessage::create($election, $recipients, $url);
            },
            DesignationTypeEnum::COMMITTEE_SUPERVISOR !== $electionType
        );

        $election->markSentNotification(Designation::NOTIFICATION_VOTE_CLOSED);
        $this->entityManager->flush();
    }

    public function notifyElectionSecondRound(Election $election): void
    {
        if (
            !$election->getDesignation()->isNotificationSecondRoundEnabled()
            || $election->isNotificationAlreadySent(Designation::NOTIFICATION_SECOND_ROUND)
        ) {
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
            $this->transactionalMailer->sendMessage(VotingPlatformElectionSecondRoundNotificationMessage::create(
                $election,
                $adherents,
                $this->getUrl($election)
            ));

            $election->markSentNotification(Designation::NOTIFICATION_SECOND_ROUND);
            $this->entityManager->flush();
        }
    }

    /**
     * @return Adherent[]
     */
    private function getAdherentForElection(Election $election, int $offset = null, int $limit = null): array
    {
        return array_map(
            function (Voter $voter) { return $voter->getAdherent(); },
            $this->voterRepository->findForElection($election, true, $offset, $limit)
        );
    }

    private function getUrl(Election $election): string
    {
        $designation = $election->getDesignation();

        if ($designation->isCopolType()) {
            return $this->urlGenerator->generate('app_territorial_council_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($designation->isExecutiveOfficeType()) {
            return $this->urlGenerator->generate('app_national_council_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($designation->isPollType()) {
            return $this->urlGenerator->generate('app_vote_statuses_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($designation->isLocalElectionType()) {
            return $this->urlGenerator->generate('app_renaissance_departmental_election_lists', ['uuid' => $designation->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($designation->isLocalElectionTypes()) {
            return $this->urlGenerator->generate('app_renaissance_local_election_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($entityElection = $election->getElectionEntity()) {
            if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $designation->getType()) {
                if ($election->isClosed()) {
                    return $this->urlGenerator->generate('app_committee_show', ['slug' => $election->getElectionEntity()->getCommittee()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
                }

                return $this->urlGenerator->generate('app_adherent_profile_activity', ['_fragment' => 'committees'], UrlGeneratorInterface::ABSOLUTE_URL);
            }

            if ($entityElection->getCommittee()) {
                return $this->urlGenerator->generate('app_committee_show', ['slug' => $election->getElectionEntity()->getCommittee()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
            }
        }

        return $this->urlGenerator->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function batchSendEmail(
        callable $getRecipientsCallback,
        callable $createMessageCallback,
        bool $chunkSending = true
    ): void {
        $offset = 0;
        $limit = 500;

        $recipients = $getRecipientsCallback($offset, $limit);

        do {
            if ($recipients) {
                $this->transactionalMailer->sendMessage($createMessageCallback($recipients));
            }

            $offset += \count($recipients);
        } while ($chunkSending && ($recipients = $getRecipientsCallback($offset, $limit)));
    }
}

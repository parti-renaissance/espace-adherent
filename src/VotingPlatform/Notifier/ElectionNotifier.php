<?php

namespace App\VotingPlatform\Notifier;

use App\Entity\Adherent;
use App\Entity\Committee;
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
        private readonly VoterRepository $voterRepository
    ) {
    }

    public function notifyElectionVoteIsOpen(Election $election): void
    {
        if (!$this->isValid($election, Designation::NOTIFICATION_VOTE_OPENED)) {
            return;
        }

        $electionType = $election->getDesignationType();
        $getRecipientsCallback = function (int $offset, int $limit) use ($election): array {
            return $this->getAdherentForElection($election, $offset, $limit);
        };
        $url = $this->getUrl($election);

        $this->batchSendEmail(
            $getRecipientsCallback,
            function (array $recipients) use ($election, $electionType, $url) {
                if (DesignationTypeEnum::POLL === $electionType) {
                    return VotingPlatformVoteStatusesIsOpenMessage::create($election, $recipients, $url);
                }

                if ($election->getDesignation()->isRenaissanceElection()) {
                    return VotingPlatformLocalElectionVoteIsOpenMessage::create($election, $recipients, $url);
                }

                return VotingPlatformElectionVoteIsOpenMessage::create($election, $recipients, $url);
            }
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
        if (!$this->isValid($election, Designation::NOTIFICATION_VOTE_REMINDER)) {
            return;
        }

        $this->transactionalMailer->sendMessage(VotingPlatformVoteReminderMessage::create(
            $election,
            $adherent,
            $this->getUrl($election)
        ));

        $this->entityManager->flush();
    }

    public function notifyElectionVoteIsOver(Election $election): void
    {
        if (!$this->isValid($election, Designation::NOTIFICATION_VOTE_CLOSED)) {
            return;
        }

        $electionType = $election->getDesignationType();
        $getRecipientsCallback = function (int $offset, int $limit) use ($election): array {
            return $this->getAdherentForElection($election, $offset, $limit);
        };

        $url = $this->getUrl($election);

        $this->batchSendEmail(
            $getRecipientsCallback,
            function (array $recipients) use ($election, $electionType, $url) {
                if (DesignationTypeEnum::POLL === $electionType) {
                    return VotingPlatformVoteStatusesIsOverMessage::create($election, $recipients, $url);
                }

                if ($election->getDesignation()->isRenaissanceElection()) {
                    return VotingPlatformLocalElectionVoteIsOverMessage::create($election, $recipients, $url);
                }

                return VotingPlatformElectionVoteIsOverMessage::create($election, $recipients, $url);
            }
        );

        $election->markSentNotification(Designation::NOTIFICATION_VOTE_CLOSED);
        $this->entityManager->flush();
    }

    public function notifyElectionSecondRound(Election $election): void
    {
        if (!$election->getDesignation()->isSecondRoundEnabled()) {
            return;
        }

        if (!$this->isValid($election, Designation::NOTIFICATION_SECOND_ROUND)) {
            return;
        }

        if ($adherents = $this->getAdherentForElection($election)) {
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
            return $this->urlGenerator->generate('app_poll_election_index', ['uuid' => $designation->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($designation->isLocalElectionType()) {
            return $this->urlGenerator->generate('app_renaissance_departmental_election_lists', ['uuid' => $designation->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($designation->isLocalElectionTypes()) {
            return $this->urlGenerator->generate('app_renaissance_local_election_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($designation->isTerritorialAssemblyType()) {
            return $this->urlGenerator->generate('app_sas_election_index', ['uuid' => $designation->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($designation->isCommitteeSupervisorType() && $election->getElectionEntity()->getCommittee()) {
            return $this->urlGenerator->generate('app_renaissance_committee_election_candidacies_lists_view', ['uuid' => $election->getElectionEntity()->getCommittee()->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->urlGenerator->generate($designation->isRenaissanceElection() ? 'app_renaissance_adherent_space' : 'homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function batchSendEmail(callable $getRecipientsCallback, callable $createMessageCallback): void
    {
        $offset = 0;
        $limit = 500;

        $recipients = $getRecipientsCallback($offset, $limit);

        do {
            if ($recipients) {
                $this->transactionalMailer->sendMessage($createMessageCallback($recipients));
            }

            $offset += \count($recipients);
        } while ($recipients = $getRecipientsCallback($offset, $limit));
    }

    private function isValid(Election $election, int $notificationBit): bool
    {
        return
            !$election->isCanceled()
            && $election->getDesignation()->isNotificationEnabled($notificationBit)
            && !$election->isNotificationAlreadySent($notificationBit);
    }
}

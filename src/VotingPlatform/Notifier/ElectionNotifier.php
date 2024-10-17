<?php

namespace App\VotingPlatform\Notifier;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Voter;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeElectionCandidacyPeriodIsOverMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\ConsultationIsOpenMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\ResultsReadyMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\VoteConfirmationMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\VoteIsOpenMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\VoteReminder1DMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\VoteReminder1HMessage;
use App\Mailer\Message\VotingPlatformElectionSecondRoundNotificationMessage;
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
                if (DesignationTypeEnum::CONSULTATION === $electionType) {
                    return ConsultationIsOpenMessage::create($election, $recipients, $url);
                }

                return VoteIsOpenMessage::create($election, $recipients, $url);
            }
        );

        $election->markSentNotification(Designation::NOTIFICATION_VOTE_OPENED);

        $this->entityManager->flush();
    }

    public function notifyCommitteeElectionCandidacyPeriodIsOver(
        Adherent $adherent,
        Designation $designation,
        Committee $committee,
    ): void {
        $this->transactionalMailer->sendMessage(CommitteeElectionCandidacyPeriodIsOverMessage::create(
            $adherent,
            $committee,
            $designation,
            $this->urlGenerator->generate('app_committee_show', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

    public function notifyVotingPlatformVoteReminder(Election $election, int $notification): void
    {
        if (!$this->isValid($election, $notification)) {
            return;
        }

        $getRecipientsCallback = function (int $offset, int $limit) use ($election): array {
            return $this->getAdherentForElection($election, $offset, $limit);
        };
        $url = $this->getUrl($election);

        $this->batchSendEmail(
            $getRecipientsCallback,
            function (array $recipients) use ($notification, $election, $url) {
                if (Designation::NOTIFICATION_VOTE_REMINDER_1H === $notification) {
                    return VoteReminder1HMessage::create($election, $recipients, $url);
                }

                return VoteReminder1DMessage::create($election, $recipients, $url);
            }
        );

        $election->markSentNotification($notification);
        $this->entityManager->flush();
    }

    public function notifyElectionVoteIsOver(Election $election): void
    {
        if (!$this->isValid($election, Designation::NOTIFICATION_VOTE_CLOSED)) {
            return;
        }

        $designation = $election->getDesignation();
        /**
         * If the election does not have a delay for displaying the results, we don't send the email for availability of the results here.
         * The email will be sent by notify CronJob.
         */
        if ($designation->isNotificationEnabled(Designation::NOTIFICATION_RESULT_READY) && !$designation->getResultScheduleDelay()) {
            return;
        }

        $getRecipientsCallback = function (int $offset, int $limit) use ($election): array {
            return $this->getAdherentForElection($election, $offset, $limit);
        };

        $url = $this->getUrl($election);

        $this->batchSendEmail(
            $getRecipientsCallback,
            function (array $recipients) use ($election, $url) {
                return ResultsReadyMessage::create($election, $recipients, $url);
            }
        );

        $election->markSentNotification(Designation::NOTIFICATION_VOTE_CLOSED);
        $this->entityManager->flush();
    }

    public function notifyForForElectionResults(Election $election): void
    {
        if (!$this->isValid($election, Designation::NOTIFICATION_RESULT_READY)) {
            return;
        }

        $getRecipientsCallback = function (int $offset, int $limit) use ($election): array {
            return $this->getAdherentForElection($election, $offset, $limit);
        };

        $url = $this->getUrl($election);

        $this->batchSendEmail(
            $getRecipientsCallback,
            function (array $recipients) use ($election, $url) {
                return ResultsReadyMessage::create($election, $recipients, $url);
            }
        );

        $election->markSentNotification(Designation::NOTIFICATION_RESULT_READY);
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

    public function notifyVoteConfirmation(Election $election, Voter $voter, string $voterKey): void
    {
        $this->transactionalMailer->sendMessage(VoteConfirmationMessage::create(
            $election,
            $voter->getAdherent(),
            $voterKey,
            $this->getUrl($election)
        ));
    }

    /**
     * @return Adherent[]
     */
    private function getAdherentForElection(Election $election, ?int $offset = null, ?int $limit = null): array
    {
        return array_map(
            function (Voter $voter) { return $voter->getAdherent(); },
            $this->voterRepository->findForElection($election, true, $offset, $limit)
        );
    }

    private function getUrl(Election $election): string
    {
        return $this->urlGenerator->generate('app_sas_election_index', ['uuid' => $election->getDesignation()->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL);
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

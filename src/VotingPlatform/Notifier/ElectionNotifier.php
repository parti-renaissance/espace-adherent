<?php

namespace App\VotingPlatform\Notifier;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Voter;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeElectionCandidacyPeriodIsOverMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\ConsultationAnnouncementMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\ConsultationIsOpenMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\ElectionIsOpenMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\ResultsReadyMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\VoteAnnouncementMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\VoteConfirmationMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\VoteIsOpenMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\VoteReminder1DMessage;
use App\Mailer\Message\Renaissance\VotingPlatform\VoteReminder1HMessage;
use App\Mailer\Message\VotingPlatformElectionSecondRoundNotificationMessage;
use App\Repository\AdherentRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use League\CommonMark\CommonMarkConverter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ElectionNotifier
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly VoterRepository $voterRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly CommonMarkConverter $markConverter,
    ) {
    }

    public function notifyElectionVoteIsOpen(Election $election): void
    {
        $electionType = $election->getDesignationType();
        $url = $this->getUrl($election);
        $description = $this->prepareDescription($election->getDesignation());

        $this->sendNotification(
            Designation::NOTIFICATION_VOTE_OPENED,
            $election,
            function (array $recipients) use ($election, $electionType, $url, $description) {
                if (DesignationTypeEnum::CONSULTATION === $electionType) {
                    return ConsultationIsOpenMessage::create($election, $recipients, $url, $description);
                }

                if (DesignationTypeEnum::VOTE === $electionType) {
                    return VoteIsOpenMessage::create($election, $recipients, $url, $description);
                }

                return ElectionIsOpenMessage::create($election, $recipients, $url, $description);
            },
            DesignationTypeEnum::CONGRESS_CN === $electionType ? function (int $offset, int $limit): array {
                return $this->adherentRepository->findAllForCongressCNElection(false, $offset, $limit);
            } : null
        );
    }

    public function notifyVoteAnnouncement(Election $election): void
    {
        $electionType = $election->getDesignationType();
        $url = $this->getUrl($election);

        if (!\in_array($electionType, [DesignationTypeEnum::CONSULTATION, DesignationTypeEnum::VOTE], true)) {
            return;
        }

        $this->sendNotification(
            Designation::NOTIFICATION_VOTE_ANNOUNCEMENT,
            $election,
            function (array $recipients) use ($election, $electionType, $url) {
                if (DesignationTypeEnum::CONSULTATION === $electionType) {
                    return ConsultationAnnouncementMessage::create($election, $recipients, $url);
                }

                return VoteAnnouncementMessage::create($election, $recipients, $url);
            }
        );
    }

    public function notifyVoteReminder(Election $election, int $notification): void
    {
        if (!\in_array($notification, [Designation::NOTIFICATION_VOTE_REMINDER_1D, Designation::NOTIFICATION_VOTE_REMINDER_1H], true)) {
            return;
        }

        $designation = $election->getDesignation();
        $zones = $election->getDesignation()->getZones()->toArray();

        if (!$designation->isCommitteeSupervisorType() && $zones) {
            $getRecipientsCallback = function (int $offset, int $limit) use ($election, $zones): array {
                return $this->adherentRepository->getAllInZonesAndNotVoted($election, $zones, $offset, $limit);
            };
        } else {
            $getRecipientsCallback = function (int $offset, int $limit) use ($election): array {
                return $this->getAdherentForElection($election, $offset, $limit, true);
            };
        }

        $url = $this->getUrl($election);

        $this->sendNotification(
            $notification,
            $election,
            function (array $recipients) use ($election, $url, $notification) {
                if (Designation::NOTIFICATION_VOTE_REMINDER_1H === $notification) {
                    return VoteReminder1HMessage::create($election, $recipients, $url);
                }

                return VoteReminder1DMessage::create($election, $recipients, $url);
            },
            $getRecipientsCallback
        );
    }

    public function notifyElectionVoteIsOver(Election $election): void
    {
        $designation = $election->getDesignation();
        /**
         * If the election does not have a delay for displaying the results, we don't send the email for availability of the results here.
         * The email will be sent by notify CronJob.
         */
        if ($designation->isNotificationEnabled(Designation::NOTIFICATION_RESULT_READY) && !$designation->getResultScheduleDelay()) {
            return;
        }

        $url = $this->getUrl($election);

        $this->sendNotification(
            Designation::NOTIFICATION_VOTE_CLOSED,
            $election,
            function (array $recipients) use ($election, $url) {
                return ResultsReadyMessage::create($election, $recipients, $url);
            }
        );
    }

    public function notifyForForElectionResults(Election $election): void
    {
        $url = $this->getUrl($election);

        $this->sendNotification(
            Designation::NOTIFICATION_RESULT_READY,
            $election,
            function (array $recipients) use ($election, $url) {
                return ResultsReadyMessage::create($election, $recipients, $url);
            }
        );
    }

    public function notifyElectionSecondRound(Election $election): void
    {
        if (!$election->getDesignation()->isSecondRoundEnabled()) {
            return;
        }

        $url = $this->getUrl($election);

        $this->sendNotification(
            Designation::NOTIFICATION_SECOND_ROUND,
            $election,
            function (array $recipients) use ($election, $url) {
                return VotingPlatformElectionSecondRoundNotificationMessage::create($election, $recipients, $url);
            }
        );
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

    private function sendNotification(int $notification, Election $election, callable $createMessageCallback, ?callable $getRecipientsCallback = null): void
    {
        if (!$this->isValid($election, $notification)) {
            return;
        }

        if (!$getRecipientsCallback) {
            $designation = $election->getDesignation();
            if (!$designation->isCommitteeSupervisorType() && $zones = $designation->getZones()->toArray()) {
                $getRecipientsCallback = function (int $offset, int $limit) use ($zones): array {
                    return $this->adherentRepository->getAllInZones($zones, true, false, $offset, $limit);
                };
            } else {
                $getRecipientsCallback = function (int $offset, int $limit) use ($election): array {
                    return $this->getAdherentForElection($election, $offset, $limit);
                };
            }
        }

        $this->batchSendEmail($getRecipientsCallback, $createMessageCallback);

        $election->markSentNotification($notification);
        $this->entityManager->flush();
    }

    /**
     * @return Adherent[]
     */
    private function getAdherentForElection(Election $election, ?int $offset = null, ?int $limit = null, bool $excludeVoted = false): array
    {
        return array_map(
            function (Voter $voter) { return $voter->getAdherent(); },
            $this->voterRepository->findForElection($election, true, $offset, $limit, $excludeVoted)
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

    private function prepareDescription(Designation $designation): string
    {
        if (!$description = $designation->getDescription() ?? $designation->wordingWelcomePage?->getContent()) {
            return '';
        }

        return $this->markConverter->convert($description)->getContent();
    }
}

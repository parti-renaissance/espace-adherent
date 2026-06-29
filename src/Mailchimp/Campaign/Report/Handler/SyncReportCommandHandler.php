<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Report\Handler;

use App\AdherentMessage\Command\CreatePublicationReachFromEmailCommand;
use App\AdherentMessage\Stats\EmailAppHitWriter;
use App\AdherentMessage\Stats\ReportSyncDelayCalculator;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpCampaignReport;
use App\Mailchimp\Campaign\Report\Command\SyncReportCommand;
use App\Mailchimp\Manager;
use App\Repository\AdherentMessageRepository;
use App\Repository\AdherentRepository;
use App\Repository\AppHitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

#[AsMessageHandler]
class SyncReportCommandHandler
{
    public function __construct(
        private readonly Manager $manager,
        private readonly AdherentMessageRepository $adherentMessageRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly EmailAppHitWriter $appHitWriter,
        private readonly AppHitRepository $appHitRepository,
        private readonly ReportSyncDelayCalculator $delayCalculator,
    ) {
    }

    public function __invoke(SyncReportCommand $command): void
    {
        if (!$adherentMessage = $this->adherentMessageRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        /** @var AdherentMessage $adherentMessage */
        if (!$adherentMessage->isSent()) {
            return;
        }

        if (3 === $command->step) {
            if ($command->firstRun && $adherentMessage->isPublication()) {
                $this->bus->dispatch(new CreatePublicationReachFromEmailCommand($adherentMessage->getUuid()));
            }

            if ($command->autoReschedule && $nextRunDelay = $this->delayCalculator->calculate($adherentMessage->getSentAt())) {
                $this->bus->dispatch(new SyncReportCommand($command->getUuid(), lowPriority: $command->lowPriority, delay: $nextRunDelay));
            }
        }

        foreach ($adherentMessage->getMailchimpCampaigns() as $campaign) {
            if (1 === $command->step) {
                $this->saveOpens($adherentMessage, $campaign);
            } elseif (2 === $command->step) {
                $this->saveClicks($adherentMessage, $campaign);
            } else {
                $this->saveGeneralStats($campaign);
            }
        }

        if ($command->step < 3) {
            $this->bus->dispatch(new SyncReportCommand($command->getUuid(), $command->firstRun, $command->autoReschedule, $command->step + 1, $command->lowPriority));
        }
    }

    private function saveOpens(AdherentMessage $adherentMessage, MailchimpCampaign $campaign): void
    {
        $this->saveEvents(
            $adherentMessage,
            fn (int $offset) => $this->manager->getReportOpenData($campaign, $offset),
            function (array $member, int $adherentId, string $objectId, \DateTimeZone $utc): array {
                $rows = [];
                foreach (array_filter($member['opens'] ?? [], static fn (array $o) => empty($o['is_proxy_open'])) as $open) {
                    $tsUtc = new \DateTimeImmutable($open['timestamp'])->setTimezone($utc);
                    $rows[] = $this->appHitWriter->buildOpenRow($adherentId, $objectId, $tsUtc);
                }

                return $rows;
            }
        );
    }

    private function saveClicks(AdherentMessage $adherentMessage, MailchimpCampaign $campaign): void
    {
        $this->saveEvents(
            $adherentMessage,
            fn (int $offset) => $this->manager->getReportClickData($campaign, $offset),
            function (array $member, int $adherentId, string $objectId, \DateTimeZone $utc): array {
                $rows = [];
                foreach (array_filter($member['activity'] ?? [], static fn (array $a) => ($a['action'] ?? null) === 'click' && !empty($a['url'])) as $click) {
                    $tsUtc = new \DateTimeImmutable($click['timestamp'])->setTimezone($utc);
                    $rows[] = $this->appHitWriter->buildClickRow($adherentId, $objectId, $click['url'], $tsUtc);
                }

                return $rows;
            }
        );

        // Flag bot/scanner clicks (>=2 links in the same second) once all click rows are persisted.
        // Single source of truth shared with the SES engagement flow (RefreshSesPublicationStatsHandler).
        $this->appHitRepository->markSuspiciousEmailClicks($adherentMessage->getUuid()->toRfc4122());
    }

    private function saveEvents(AdherentMessage $adherentMessage, callable $fetchPage, callable $extractRows): void
    {
        $objectId = $adherentMessage->getUuid()->toRfc4122();
        $offset = 0;
        $utc = new \DateTimeZone('UTC');

        while (true) {
            /** @var array<array> $members */
            $members = $fetchPage($offset);
            if (!$members) {
                break;
            }

            $emails = array_values(array_unique(array_column($members, 'email_address')));
            $emailToAdh = $this->adherentRepository->mapIdsByEmails($emails);

            $rows = [];
            foreach ($members as $member) {
                $email = $member['email_address'] ?? null;
                if (!$email) {
                    continue;
                }

                $adherentId = $emailToAdh[$email] ?? null;
                if (!$adherentId) {
                    continue;
                }

                foreach ($extractRows($member, $adherentId, $objectId, $utc) as $row) {
                    $rows[] = $row;
                }
            }

            if ($rows) {
                $this->appHitWriter->insertHits($rows);
            }

            $offset += \count($members);
            sleep(1);
        }
    }

    private function saveGeneralStats(MailchimpCampaign $campaign): void
    {
        if (empty($data = $this->manager->getReportData($campaign))) {
            return;
        }

        $report = $campaign->getReport() ?? new MailchimpCampaignReport();

        $report->setOpenTotal($this->propertyAccessor->getValue($data, '[opens][proxy_excluded_opens]'));
        $report->setOpenUnique($this->propertyAccessor->getValue($data, '[opens][proxy_excluded_unique_opens]'));
        $report->setOpenRate(($rate = $this->propertyAccessor->getValue($data, '[opens][proxy_excluded_open_rate]')) ? round($rate * 100.0, 2) : 0);
        $report->setLastOpen(
            ($date = $this->propertyAccessor->getValue($data, '[opens][last_open]')) ?
                new \DateTime($date) :
                null
        );

        $report->setClickTotal($this->propertyAccessor->getValue($data, '[clicks][clicks_total]'));
        $report->setClickUnique($this->propertyAccessor->getValue($data, '[clicks][unique_clicks]'));
        $report->setClickRate(($rate = $this->propertyAccessor->getValue($data, '[clicks][click_rate]')) ? round($rate * 100.0, 2) : 0);
        $report->setLastClick(
            ($date = $this->propertyAccessor->getValue($data, '[clicks][last_click]')) ?
                new \DateTime($date) :
                null
        );

        $report->setEmailSent($emailSent = $this->propertyAccessor->getValue($data, '[emails_sent]'));
        $report->setUnsubscribed($unsub = $this->propertyAccessor->getValue($data, '[unsubscribed]'));
        $report->setUnsubscribedRate($emailSent > 0 ? round($unsub * 100.0 / $emailSent, 2) : 0);

        $campaign->setReport($report);
        $this->entityManager->flush();
    }
}

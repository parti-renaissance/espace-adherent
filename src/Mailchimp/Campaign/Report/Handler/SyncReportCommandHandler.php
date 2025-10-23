<?php

namespace App\Mailchimp\Campaign\Report\Handler;

use App\AdherentMessage\Command\CreatePublicationReachFromEmailCommand;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpCampaignReport;
use App\JeMengage\Hit\EventTypeEnum;
use App\JeMengage\Hit\SourceGroupEnum;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Mailchimp\Campaign\Report\Command\SyncReportCommand;
use App\Mailchimp\Manager;
use App\Repository\AdherentMessageRepository;
use App\Repository\AdherentRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
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

        if ($command->firstRun && AdherentMessageInterface::SOURCE_VOX === $adherentMessage->getSource()) {
            $this->bus->dispatch(new CreatePublicationReachFromEmailCommand($adherentMessage->getUuid()));
        }

        foreach ($adherentMessage->getMailchimpCampaigns() as $campaign) {
            $this->saveOpens($adherentMessage, $campaign);
            $this->saveClicks($adherentMessage, $campaign);
            $this->saveGeneralStats($campaign);
        }

        if ($nextRunDelay = $this->calculateDelay($adherentMessage->getSentAt())) {
            $this->bus->dispatch(new SyncReportCommand($command->getUuid()), [new DelayStamp($nextRunDelay)]);
        }
    }

    private function calculateDelay(\DateTimeInterface $originTs): ?int
    {
        $age = (new \DateTime())->getTimestamp() - $originTs->getTimestamp();

        $min = 60;
        $hour = 3600;
        $day = 86400;

        return match (true) {
            $age < 1 * $hour => 5 * $min * 1000,   // 5 min
            $age < 6 * $hour => 10 * $min * 1000,   // 10 min
            $age < 3 * $day => 1 * $hour * 1000,  // 1 h
            $age < 14 * $day => 1 * $day * 1000,  // 1 j
            default => null,               // stop
        };
    }

    private function saveOpens(AdherentMessage $adherentMessage, MailchimpCampaign $campaign): void
    {
        $this->saveEvents(
            $adherentMessage,
            fn (int $offset) => $this->manager->getReportOpenData($campaign, $offset),
            function (array $member, int $adherentId, string $objectId, \DateTimeZone $utc): array {
                $rows = [];
                foreach (array_filter($member['opens'] ?? [], static fn (array $o) => empty($o['is_proxy_open'])) as $open) {
                    $tsUtc = (new \DateTimeImmutable($open['timestamp']))->setTimezone($utc);
                    $rows[] = [
                        'event_type' => EventTypeEnum::Open->value,
                        'source' => 'email',
                        'object_type' => TargetTypeEnum::Publication->value,
                        'object_id' => $objectId,
                        'app_date' => $tsUtc->format('Y-m-d H:i:s'),
                        'fingerprint' => $this->buildFingerprint([$adherentId, 'email', 'open', $objectId, $tsUtc->format('c')]),
                    ];
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
                    $tsUtc = (new \DateTimeImmutable($click['timestamp']))->setTimezone($utc);
                    $rows[] = [
                        'event_type' => EventTypeEnum::Click->value,
                        'source' => 'email',
                        'target_url' => $url = $click['url'],
                        'object_type' => TargetTypeEnum::Publication->value,
                        'object_id' => $objectId,
                        'app_date' => $tsUtc->format('Y-m-d H:i:s'),
                        'fingerprint' => $this->buildFingerprint([$adherentId, 'email', 'click', $url, $objectId, $tsUtc->format('c')]),
                    ];
                }

                return $rows;
            }
        );
    }

    private function saveEvents(AdherentMessage $adherentMessage, callable $fetchPage, callable $extractRows): void
    {
        $conn = $this->entityManager->getConnection();
        $objectId = $adherentMessage->getUuid()->toString();
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

                $eventRows = $extractRows($member, $adherentId, $objectId, $utc);
                if (!$eventRows) {
                    continue;
                }

                foreach ($eventRows as $r) {
                    $rows[] = ['adherent_id' => $adherentId, ...$r];
                }
            }

            if ($rows) {
                $this->insertBatchAppHits($conn, $rows);
            }

            $offset += \count($members);
            $this->entityManager->clear();
            sleep(1);
        }
    }

    private function buildFingerprint(array $parts): string
    {
        return hash('sha256', implode('|', $parts));
    }

    private function insertBatchAppHits(Connection $conn, array $rows): void
    {
        if (!$rows) {
            return;
        }

        $autoCols = ['uuid', 'activity_session_uuid', 'created_at', 'updated_at'];
        $dynamicCols = array_keys(reset($rows));
        $cols = array_values(array_unique(array_merge($autoCols, $dynamicCols)));

        $placeholders = [];
        $params = [];

        $nowUtc = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        foreach ($rows as $r) {
            $autoGeneratedData = [
                'uuid' => Uuid::uuid4()->toString(),
                'activity_session_uuid' => Uuid::uuid4()->toString(),
                'created_at' => $nowUtc,
                'updated_at' => $nowUtc,
                'source_group' => SourceGroupEnum::Email->value,
            ];

            $placeholders[] = '('.implode(',', array_fill(0, \count($cols), '?')).')';

            foreach ($cols as $c) {
                if (\array_key_exists($c, $autoGeneratedData)) {
                    $params[] = $autoGeneratedData[$c];
                } else {
                    $params[] = $r[$c] ?? null;
                }
            }
        }

        $sql = \sprintf(
            'INSERT INTO app_hit (%s) VALUES %s ON DUPLICATE KEY UPDATE fingerprint = VALUES(fingerprint)',
            implode(',', $cols),
            implode(',', $placeholders)
        );

        $conn->executeStatement($sql, $params);
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

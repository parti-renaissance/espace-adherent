<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Handler;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Command\RetrySendMailchimpCampaignCommand;
use App\Mailchimp\Manager;
use App\Repository\MailchimpCampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final class RetrySendMailchimpCampaignCommandHandler
{
    private const int MAX_RETRIES = 6;
    private const array DELAY_SCHEDULE_MS = [
        30_000,    // 30s
        60_000,    // 1min
        300_000,   // 5min
        600_000,   // 10min
        1_800_000, // 30min
        3_600_000, // 60min
    ];

    public function __construct(
        private readonly MailchimpCampaignRepository $repository,
        private readonly Manager $manager,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(RetrySendMailchimpCampaignCommand $command): void
    {
        $campaign = $this->repository->find($command->campaignId);

        if (!$campaign instanceof MailchimpCampaign) {
            return;
        }

        $campaign->incrementRetryCount();

        $success = $this->manager->retrySendCampaign($campaign);

        $campaign->addRetryAttempt($success, $success ? null : $campaign->getDetail());

        $this->entityManager->flush();

        if ($success) {
            $this->logger->info('[Mailchimp] Campaign retry succeeded', [
                'campaignId' => $command->campaignId,
                'retryCount' => $campaign->getRetryCount(),
            ]);

            return;
        }

        if ($command->countRetry < self::MAX_RETRIES) {
            $delay = self::DELAY_SCHEDULE_MS[$command->countRetry] ?? self::DELAY_SCHEDULE_MS[array_key_last(self::DELAY_SCHEDULE_MS)];

            $this->bus->dispatch(
                new RetrySendMailchimpCampaignCommand($command->campaignId, $command->countRetry + 1),
                [new DelayStamp($delay)]
            );

            $this->logger->warning('[Mailchimp] Campaign retry scheduled', [
                'campaignId' => $command->campaignId,
                'retryCount' => $command->countRetry + 1,
                'delayMs' => $delay,
            ]);
        } else {
            $context = [
                'campaignId' => $command->campaignId,
                'externalId' => $campaign->getExternalId(),
                'staticSegmentId' => $campaign->getStaticSegmentId(),
                'messageUuid' => $campaign->getMessage()->getUuid()->toString(),
                'lastError' => $campaign->getDetail(),
                'retryCount' => $campaign->getRetryCount(),
            ];
            $this->logger->error('[Mailchimp] Campaign retry exhausted', $context);

            throw new \RuntimeException(\sprintf('Mailchimp campaign %d retry exhausted after %d attempts (external_id=%s)', $command->campaignId, $campaign->getRetryCount(), $campaign->getExternalId() ?? 'null'));
        }
    }
}

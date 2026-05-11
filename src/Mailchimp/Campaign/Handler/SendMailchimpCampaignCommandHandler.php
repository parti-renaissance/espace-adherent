<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Handler;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Command\RetrySendMailchimpCampaignCommand;
use App\Mailchimp\Campaign\Command\SendMailchimpCampaignCommand;
use App\Mailchimp\Driver;
use App\Mailchimp\Manager;
use App\Repository\MailchimpCampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class SendMailchimpCampaignCommandHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly MailchimpCampaignRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Driver $driver,
        private readonly Manager $manager,
        private readonly MessageBusInterface $bus,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(SendMailchimpCampaignCommand $command): void
    {
        $campaign = $this->repository->find($command->campaignId);
        if (!$campaign instanceof MailchimpCampaign) {
            $this->logger->warning('[SendMailchimpCampaign] MailchimpCampaign not found', [
                'campaign_id' => $command->campaignId,
            ]);

            return;
        }

        $this->entityManager->refresh($campaign);

        if (\in_array($campaign->status, [MailchimpStatusEnum::Sent, MailchimpStatusEnum::Sending], true)) {
            return;
        }

        $externalId = $campaign->getExternalId();
        if (null === $externalId) {
            $this->logger->error('[SendMailchimpCampaign] Missing external id — send aborted', [
                'campaign_id' => $campaign->getId(),
            ]);

            return;
        }

        $localSegmentId = $campaign->getStaticSegmentId();
        $remoteSegmentId = $this->driver->getCampaignSavedSegmentId($externalId);

        if ($remoteSegmentId !== $localSegmentId) {
            $detail = \sprintf('Segment mismatch: local=%s remote=%s', $localSegmentId ?? 'null', $remoteSegmentId ?? 'null');

            $this->logger->error('[SendMailchimpCampaign] Segment mismatch — send aborted', [
                'campaign_id' => $campaign->getId(),
                'external_id' => $externalId,
                'local_segment_id' => $localSegmentId,
                'remote_segment_id' => $remoteSegmentId,
            ]);

            $campaign->markAsError($detail);
            $this->entityManager->flush();

            throw new \RuntimeException(\sprintf('Mailchimp campaign %d: %s (external_id=%s)', $campaign->getId(), $detail, $externalId));
        }

        if (!$this->manager->sendMailchimpCampaign($campaign)) {
            $this->bus->dispatch(
                new RetrySendMailchimpCampaignCommand($campaign->getId()),
                [new DelayStamp(30_000)]
            );
        }
    }
}

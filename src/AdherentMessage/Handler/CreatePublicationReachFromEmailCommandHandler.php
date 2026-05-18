<?php

declare(strict_types=1);

namespace App\AdherentMessage\Handler;

use App\AdherentMessage\Command\CreatePublicationReachFromEmailCommand;
use App\AdherentMessage\MailchimpStatusEnum;
use App\Doctrine\Utils\BulkInsertHelper;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Mailchimp\Manager;
use App\Repository\AdherentMessageRepository;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class CreatePublicationReachFromEmailCommandHandler
{
    public function __construct(
        private readonly AdherentMessageRepository $adherentMessageRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly Manager $manager,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly BulkInsertHelper $bulkInsertHelper,
    ) {
    }

    public function __invoke(CreatePublicationReachFromEmailCommand $command): void
    {
        if (!$adherentMessage = $this->adherentMessageRepository->findOneByUuid($command->getUuid()->toRfc4122())) {
            return;
        }

        foreach ($adherentMessage->getMailchimpCampaigns() as $campaign) {
            if (MailchimpStatusEnum::Sent !== $campaign->status && ($status = $this->manager->getCampaignStatus($campaign)) && $campaign->status !== $status) {
                $campaign->status = $status;
                $this->entityManager->flush();
            }

            if (MailchimpStatusEnum::Sent === $campaign->status) {
                $this->saveEvents($adherentMessage, fn (int $offset) => $this->manager->getReportSentData($campaign, $offset));
            } elseif ($command->countRetry < 5) {
                $this->bus->dispatch(new CreatePublicationReachFromEmailCommand($command->getUuid(), $command->countRetry + 1), [new DelayStamp(5000)]);
            }
        }
    }

    private function saveEvents(AdherentMessage $adherentMessage, callable $fetchPage): void
    {
        $objectId = $adherentMessage->getId();
        $offset = 0;
        $now = ($adherentMessage->getSentAt() ?? new \DateTime())->format('Y-m-d H:i:s');

        while (true) {
            /** @var array<array> $members */
            $members = $fetchPage($offset);
            if (!$members) {
                break;
            }

            $emails = array_values(array_unique(array_column($members, 'email_address')));

            $rows = [];
            foreach ($this->adherentRepository->mapIdsByEmails($emails) as $adherentId) {
                $rows[] = [
                    'message_id' => $objectId,
                    'adherent_id' => $adherentId,
                    'source' => 'email',
                    'date' => $now,
                ];
            }

            if ($rows) {
                $this->bulkInsertHelper->insertIgnore('adherent_message_reach', $rows);
            }

            $offset += \count($members);
            $this->entityManager->clear();
            sleep(1);
        }
    }
}

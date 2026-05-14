<?php

declare(strict_types=1);

namespace App\AdherentMessage\Handler;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Manager;
use App\Repository\AdherentMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
class AdherentMessageChangeCommandHandler
{
    public function __construct(
        private readonly AdherentMessageRepository $repository,
        private readonly Manager $mailchimpManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(AdherentMessageChangeCommand $command): void
    {
        if (!$message = $this->getMessage($command->getUuid())) {
            return;
        }

        if ($message->isSynchronized()) {
            return;
        }

        $filter = $message->getFilter();

        foreach ($message->getMailchimpCampaigns() as $mailchimpCampaign) {
            if ($this->mailchimpManager->editCampaign($mailchimpCampaign)) {
                $filter?->setSynchronized(true);

                $this->entityManager->flush();

                if ($this->mailchimpManager->editCampaignContent($mailchimpCampaign)) {
                    $mailchimpCampaign->setSynchronized(true);
                }

                $this->entityManager->flush();
            }
        }

        $this->entityManager->clear();
    }

    private function getMessage(Uuid $uuid): ?AdherentMessageInterface
    {
        /** @var AdherentMessageInterface $message */
        $message = $this->repository->findOneByUuid($uuid->toRfc4122());

        if (!$message) {
            return null;
        }

        $this->entityManager->refresh($message);

        if ($filter = $message->getFilter()) {
            $this->entityManager->refresh($filter);
        }

        foreach ($message->getMailchimpCampaigns() as $campaign) {
            $this->entityManager->refresh($campaign);
        }

        return $message;
    }
}

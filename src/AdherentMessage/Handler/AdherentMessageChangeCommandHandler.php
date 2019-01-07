<?php

namespace AppBundle\AdherentMessage\Handler;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Mailchimp\Manager;
use AppBundle\Repository\AdherentMessageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AdherentMessageChangeCommandHandler implements MessageHandlerInterface
{
    private $repository;
    private $mailchimpManager;
    private $entityManager;

    public function __construct(
        AdherentMessageRepository $repository,
        Manager $mailchimpManager,
        ObjectManager $entityManager
    ) {
        $this->repository = $repository;
        $this->mailchimpManager = $mailchimpManager;
        $this->entityManager = $entityManager;
    }

    public function __invoke(AdherentMessageChangeCommand $command): void
    {
        /** @var AdherentMessageInterface $message */
        $message = $this->repository->findOneByUuid($command->getUuid()->toString());

        if (!$message || $message->isSynchronized()) {
            return;
        }

        $needPersistCampaignId = null === $message->getExternalId();

        if ($this->mailchimpManager->editCampaign($message)) {
            if ($needPersistCampaignId) {
                $this->entityManager->flush();
            }

            if ($this->mailchimpManager->editCampaignContent($message)) {
                $message->setSynchronized(true);
                $this->entityManager->flush();
            }
        }
    }
}

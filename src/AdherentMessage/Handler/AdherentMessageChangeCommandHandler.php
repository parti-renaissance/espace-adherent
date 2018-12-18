<?php

namespace AppBundle\AdherentMessage\Handler;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Mailchimp\Manager;
use AppBundle\Repository\AdherentMessageRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AdherentMessageChangeCommandHandler implements MessageHandlerInterface
{
    private $repository;
    private $mailchimpManager;

    public function __construct(AdherentMessageRepository $repository, Manager $mailchimpManager)
    {
        $this->repository = $repository;
        $this->mailchimpManager = $mailchimpManager;
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
                $this->repository->flush();
            }

            if ($this->mailchimpManager->editCampaignContent($message)) {
                $this->repository->markAsSynchronized($message);
            }
        }
    }
}

<?php

namespace App\Adherent\Tag\Handler;

use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Adherent\Tag\TagAggregator;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class RefreshAdherentTagHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentRepository $adherentRepository,
        private readonly TagAggregator $tagAggregator,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(RefreshAdherentTagCommand $command): void
    {
        if (!$adherent = $this->adherentRepository->findByUuid($command->getUuid())) {
            return;
        }

        $this->entityManager->refresh($adherent);

        $adherent->tags = $this->tagAggregator->getTags($adherent);

        $this->entityManager->flush();

        $this->bus->dispatch(new AdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress()));
    }
}

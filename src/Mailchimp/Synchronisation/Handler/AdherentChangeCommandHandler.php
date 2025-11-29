<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Handler;

use App\Entity\Adherent;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommandInterface;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AdherentChangeCommandHandler
{
    public function __construct(
        private readonly Manager $manager,
        private readonly AdherentRepository $repository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(AdherentChangeCommandInterface $message): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $this->repository->findOneByUuid($message->getUuid()->toString())) {
            return;
        }

        if ($adherent->isPending()) {
            return;
        }

        $this->entityManager->refresh($adherent);

        $this->manager->editMember($adherent, $message);

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}

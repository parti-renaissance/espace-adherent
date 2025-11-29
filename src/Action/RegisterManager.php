<?php

declare(strict_types=1);

namespace App\Action;

use App\Entity\Action\Action;
use App\Entity\Adherent;
use App\Repository\Action\ActionParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;

class RegisterManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ActionParticipantRepository $actionParticipantRepository,
    ) {
    }

    public function register(Action $action, Adherent $adherent): void
    {
        if ($action->isCancelled()) {
            return;
        }

        if ($this->actionParticipantRepository->findOneBy(['action' => $action, 'adherent' => $adherent])) {
            return;
        }

        $action->addNewParticipant($adherent);
        $this->entityManager->flush();
    }

    public function unregister(Action $action, Adherent $adherent): void
    {
        if (!$participant = $this->actionParticipantRepository->findOneBy(['action' => $action, 'adherent' => $adherent])) {
            return;
        }

        $this->entityManager->remove($participant);
        $this->entityManager->flush();
    }
}

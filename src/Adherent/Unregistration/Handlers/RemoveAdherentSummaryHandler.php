<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;
use App\Repository\SummaryRepository;
use Doctrine\ORM\EntityManagerInterface;

class RemoveAdherentSummaryHandler implements UnregistrationAdherentHandlerInterface
{
    private $manager;
    private $repository;

    public function __construct(EntityManagerInterface $manager, SummaryRepository $repository)
    {
        $this->manager = $manager;
        $this->repository = $repository;
    }

    public function supports(Adherent $adherent): bool
    {
        return null !== $this->repository->findOneForAdherent($adherent);
    }

    public function handle(Adherent $adherent): void
    {
        $this->manager->remove($this->repository->findOneForAdherent($adherent));
        $this->manager->flush();
    }
}

<?php

namespace App\Membership;

use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;

class AdminMembershipHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createNewAdherent(): Adherent
    {
        return Adherent::createBlank();
    }

    public function handleCreate(Adherent $adherent): void
    {
        $this->entityManager->persist($adherent);
        $this->entityManager->flush();
    }
}

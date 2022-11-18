<?php

namespace App\Renaissance\Membership\Admin;

use App\Membership\AdherentFactory;
use Doctrine\ORM\EntityManagerInterface;

class AdherentCreateCommandHandler
{
    private AdherentFactory $adherentFactory;
    private EntityManagerInterface $entityManager;

    public function __construct(AdherentFactory $adherentFactory, EntityManagerInterface $entityManager)
    {
        $this->adherentFactory = $adherentFactory;
        $this->entityManager = $entityManager;
    }

    public function createCommand(): AdherentCreateCommand
    {
        return new AdherentCreateCommand();
    }

    public function handle(AdherentCreateCommand $command): void
    {
        $adherent = $this->adherentFactory->createFromAdminAdherentCreateCommand($command);

        $this->entityManager->persist($adherent);
        $this->entityManager->flush();
    }
}

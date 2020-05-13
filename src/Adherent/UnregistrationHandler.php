<?php

namespace App\Adherent;

use App\Adherent\Command\RemoveAdherentAndRelatedDataCommand;
use App\Adherent\Unregistration\UnregistrationCommand;
use App\Adherent\Unregistration\UnregistrationFactory;
use App\Entity\Adherent;
use App\Entity\Unregistration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UnregistrationHandler
{
    private $bus;
    private $entityManager;

    public function __construct(MessageBusInterface $bus, EntityManagerInterface $entityManager)
    {
        $this->bus = $bus;
        $this->entityManager = $entityManager;
    }

    public function handle(Adherent $adherent, UnregistrationCommand $command = null): void
    {
        if ($command) {
            $unregistration = UnregistrationFactory::createFromUnregistrationCommandAndAdherent($command, $adherent);
        } else {
            $unregistration = Unregistration::createFromAdherent($adherent);
        }

        $adherent->markAsToDelete();

        $this->entityManager->persist($unregistration);
        $this->entityManager->flush();

        $this->bus->dispatch(new RemoveAdherentAndRelatedDataCommand($unregistration->getUuid()));
    }
}

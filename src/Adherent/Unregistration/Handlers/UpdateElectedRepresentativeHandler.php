<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeChangeCommand;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeDeleteCommand;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateElectedRepresentativeHandler implements UnregistrationAdherentHandlerInterface
{
    private $bus;
    private $manager;
    private $electedRepresentativeRepository;

    public function __construct(
        MessageBusInterface $bus,
        EntityManagerInterface $manager,
        ElectedRepresentativeRepository $electedRepresentativeRepository
    ) {
        $this->bus = $bus;
        $this->manager = $manager;
        $this->electedRepresentativeRepository = $electedRepresentativeRepository;
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function handle(Adherent $adherent): void
    {
        /** @var ElectedRepresentative $electedRepresentative */
        if (!$electedRepresentative = $this->electedRepresentativeRepository->findOneBy(['adherent' => $adherent])) {
            return;
        }

        $electedRepresentative->removeAdherent();

        $this->manager->flush();

        $this->bus->dispatch(new ElectedRepresentativeDeleteCommand($adherent->getEmailAddress()));

        if ($electedRepresentative->getEmailAddress()) {
            $this->bus->dispatch(new ElectedRepresentativeChangeCommand($electedRepresentative->getUuid()));
        }
    }
}

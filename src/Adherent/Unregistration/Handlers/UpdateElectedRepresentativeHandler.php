<?php

namespace App\Adherent\Unregistration\Handlers;

use App\ElectedRepresentative\ElectedRepresentativeEvent;
use App\ElectedRepresentative\ElectedRepresentativeEvents;
use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateElectedRepresentativeHandler implements UnregistrationAdherentHandlerInterface
{
    private $dispatcher;
    private $manager;
    private $electedRepresentativeRepository;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $manager,
        ElectedRepresentativeRepository $electedRepresentativeRepository
    ) {
        $this->dispatcher = $dispatcher;
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

        $this->dispatcher->dispatch(ElectedRepresentativeEvents::BEFORE_UPDATE, new ElectedRepresentativeEvent(clone $electedRepresentative));

        $electedRepresentative->removeAdherent();

        $this->manager->flush();

        $this->dispatcher->dispatch(ElectedRepresentativeEvents::POST_UPDATE, new ElectedRepresentativeEvent($electedRepresentative));
    }
}

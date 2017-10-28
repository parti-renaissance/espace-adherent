<?php

namespace AppBundle\MoocEvent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\MoocEvent;
use AppBundle\Events;
use AppBundle\Repository\MoocEventRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MoocEventManager
{
    private $manager;
    private $dispatcher;
    private $repository;

    public function __construct(ObjectManager $manager, EventDispatcherInterface $dispatcher, MoocEventRepository $repository)
    {
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
        $this->repository = $repository;
    }

    public function updateMoocEvent(MoocEvent $moocEvent): void
    {
        if (!$moocEvent->getId()) {
            $this->manager->persist($moocEvent);
        }

        $this->manager->flush();
    }

    public function publishMoocEvent(MoocEvent $moocEvent): void
    {
        $moocEvent->publish();

        $this->checkPublicationMoocEvent($moocEvent, false);

        $this->manager->flush();
    }

    public function checkPublicationMoocEvent(MoocEvent $moocEvent, bool $flush = true): void
    {
        if ($moocEvent->isPublished() && !$moocEvent->wasPublished()) {
            $moocEvent->setWasPublished(true);

            if ($flush) {
                $this->manager->flush();
            }

            $this->dispatcher->dispatch(Events::MOOC_EVENT_VALIDATED, new MoocEventValidatedEvent(
                $moocEvent
            ));
        }
    }

    public function removeOrganizerCitizenInitiatives(Adherent $adherent): void
    {
        $this->repository->removeOrganizerEvents($adherent, MoocEventRepository::TYPE_PAST, true);
        $this->repository->removeOrganizerEvents($adherent, MoocEventRepository::TYPE_UPCOMING);
    }
}

<?php

namespace AppBundle\Controller\EnMarche\EventManager;

use AppBundle\Entity\MunicipalEvent;
use AppBundle\Event\EventManagerSpaceEnum;
use AppBundle\Repository\MunicipalEventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-municipales-2020", name="app_municipal_chief_event_manager_")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class EventManagerMunicipalChief extends AbstractEventManagerController
{
    private $repository;

    public function __construct(MunicipalEventRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::MUNICIPAL_CHIEF;
    }

    protected function getEvents(string $type = null): array
    {
        return $this->repository->findEventsByOrganizer($this->getUser());
    }

    protected function getEventClassName(): string
    {
        return MunicipalEvent::class;
    }
}

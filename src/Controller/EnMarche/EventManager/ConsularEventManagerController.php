<?php

namespace AppBundle\Controller\EnMarche\EventManager;

use AppBundle\Event\EventManagerSpaceEnum;
use AppBundle\Repository\ConsularEventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-consulaire", name="app_consular_event_manager_")
 *
 * @Security("is_granted('ROLE_CONSULAR')")
 */
class ConsularEventManagerController extends AbstractEventManagerController
{
    private $repository;

    public function __construct(ConsularEventRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::CONSULAR;
    }

    protected function getEvents(string $type = null): array
    {
        return $this->repository->findEventsByOrganizer($this->getUser());
    }

    protected function getEventClassName(): string
    {
        return ConsularEvent::class;
    }
}

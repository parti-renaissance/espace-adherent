<?php

namespace App\Controller\EnMarche\EventManager;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\Event\MunicipalEvent;
use App\Event\EventManagerSpaceEnum;
use App\Repository\MunicipalEventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-municipales-2020", name="app_municipal_chief_event_manager_")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefEventManagerController extends AbstractEventManagerController
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

    protected function getEventsPaginator(Adherent $adherent, string $type = null, int $page = 1): PaginatorInterface
    {
        return $this->repository->findEventsByOrganizerPaginator($adherent, $page);
    }

    protected function getEventClassName(): string
    {
        return MunicipalEvent::class;
    }
}

<?php

namespace AppBundle\Controller\EnMarche\Election\VoteResultList;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\City;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-responsable-communal/assesseurs/listes", name="app_vote_result_list_municipal_manager")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_MANAGER')")
 */
class MunicipalManagerVoteResultListController extends AbstractVoteResultListController
{
    /**
     * @Route(name="_edit", methods={"GET", "POST"})
     */
    public function editVoteResultListAction(Request $request): Response
    {
        /** @var Adherent $adherent */
        $listCollection = $this->findListCollection($cities = $this->findAdherentCities());

        return $this->submitListFormAction($request, $cities, $listCollection);
    }

    /**
     * @return City[]
     */
    private function findAdherentCities(): array
    {
        return $this->getUser()->getMunicipalManagerRole()->getCities()->toArray();
    }

    protected function getSpaceType(): string
    {
        return 'municipal_manager';
    }
}

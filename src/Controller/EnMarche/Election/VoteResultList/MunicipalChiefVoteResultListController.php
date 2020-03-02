<?php

namespace AppBundle\Controller\EnMarche\Election\VoteResultList;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\City;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Repository\CityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-municipales-2020/assesseurs/listes", name="app_vote_result_list_municipal_chief")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefVoteResultListController extends AbstractVoteResultListController
{
    private $cityRepository;

    public function __construct(CityRepository $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }

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
        $inseeCode = $this->getUser()->getMunicipalChiefManagedArea()->getInseeCode();
        $inseeCodes = [$inseeCode];

        if (isset(FranceCitiesBundle::SPECIAL_CITY_DISTRICTS[$inseeCode])) {
            $inseeCodes = array_keys(FranceCitiesBundle::SPECIAL_CITY_DISTRICTS[$inseeCode]);
        }

        return $this->cityRepository->findByInseeCodes($inseeCodes);
    }

    protected function getSpaceType(): string
    {
        return 'municipal_chief';
    }
}

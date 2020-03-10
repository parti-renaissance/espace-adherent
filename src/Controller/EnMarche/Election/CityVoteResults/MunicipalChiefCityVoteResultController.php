<?php

namespace AppBundle\Controller\EnMarche\Election\CityVoteResults;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-municipales-2020", name="app_municipal_chief")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefCityVoteResultController extends AbstractCityVoteResultController
{
    protected function getSpaceType(): string
    {
        return 'municipal_chief';
    }
}

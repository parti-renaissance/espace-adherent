<?php

namespace App\Controller\EnMarche\Election\CityVoteResults;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-responsable-communal", name="app_municipal_manager")
 *
 * @IsGranted("ROLE_MUNICIPAL_MANAGER")
 */
class MunicipalManagerCityVoteResultController extends AbstractCityVoteResultController
{
    protected function getSpaceType(): string
    {
        return 'municipal_manager';
    }
}

<?php

namespace App\Controller\EnMarche\Election\CityVoteResults;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent", name="app_referent")
 *
 * @IsGranted("ROLE_REFERENT")
 */
class ReferentCityVoteResultController extends AbstractCityVoteResultController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }
}

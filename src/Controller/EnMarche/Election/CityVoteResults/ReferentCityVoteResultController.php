<?php

namespace App\Controller\EnMarche\Election\CityVoteResults;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent", name="app_referent")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentCityVoteResultController extends AbstractCityVoteResultController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }
}

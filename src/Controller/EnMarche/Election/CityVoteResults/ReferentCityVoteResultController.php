<?php

namespace App\Controller\EnMarche\Election\CityVoteResults;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_REFERENT')]
#[Route(path: '/espace-referent', name: 'app_referent')]
class ReferentCityVoteResultController extends AbstractCityVoteResultController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }
}

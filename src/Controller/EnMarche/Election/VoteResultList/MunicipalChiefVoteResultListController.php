<?php

namespace App\Controller\EnMarche\Election\VoteResultList;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-municipales-2020/assesseurs/communes", name="app_municipal_chief")
 *
 * @IsGranted("ROLE_MUNICIPAL_CHIEF")
 */
class MunicipalChiefVoteResultListController extends AbstractVoteResultListController
{
    protected function getSpaceType(): string
    {
        return 'municipal_chief';
    }
}

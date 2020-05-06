<?php

namespace App\Controller\EnMarche\Election\VoteResultList;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-municipales-2020/assesseurs/communes", name="app_municipal_chief")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefVoteResultListController extends AbstractVoteResultListController
{
    protected function getSpaceType(): string
    {
        return 'municipal_chief';
    }
}

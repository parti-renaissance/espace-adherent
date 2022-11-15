<?php

namespace App\Controller\EnMarche\Election\VoteResultList;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-responsable-communal/assesseurs/communes", name="app_municipal_manager")
 *
 * @IsGranted("ROLE_MUNICIPAL_MANAGER")
 */
class MunicipalManagerVoteResultListController extends AbstractVoteResultListController
{
    protected function getSpaceType(): string
    {
        return 'municipal_manager';
    }

    protected function getSuccessRedirectionResponse(): RedirectResponse
    {
        return $this->redirectToRoute('app_municipal_manager_cities_list');
    }
}

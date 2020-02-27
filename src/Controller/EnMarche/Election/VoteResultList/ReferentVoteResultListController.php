<?php

namespace AppBundle\Controller\EnMarche\Election\VoteResultList;

use AppBundle\Entity\City;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent/assesseurs/communes", name="app_vote_result_list_referent")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentVoteResultListController extends AbstractVoteResultListController
{
    /**
     * @Route("/{id}/listes", name="_edit", methods={"GET", "POST"})
     */
    public function editVoteResultListAction(City $city, Request $request): Response
    {
        $listCollection = $this->findListCollection([$city]);

        return $this->submitListFormAction($request, [$city], $listCollection);
    }

    protected function getSuccessRedirectionResponse(): RedirectResponse
    {
        return $this->redirectToRoute('app_assessors_referent_municipal_manager_attribution_form');
    }

    protected function getSpaceType(): string
    {
        return 'referent';
    }
}

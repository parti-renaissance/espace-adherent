<?php

namespace App\Controller\Api\AdherentFilter;

use App\AdherentFilter\AdherentFiltersGenerator;
use App\Scope\AuthorizationChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v3/adherents/filters", name="app_adherents_filters_get", methods={"GET"})
 *
 * @Security("is_granted('IS_AUTHENTICATED_FULLY') and is_granted('REQUEST_SCOPE_GRANTED')")
 */
class GetFilterCollectionController extends AbstractController
{
    private AuthorizationChecker $authorizationChecker;
    private AdherentFiltersGenerator $builder;

    public function __construct(AuthorizationChecker $authorizationChecker, AdherentFiltersGenerator $builder)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->builder = $builder;
    }

    public function __invoke(Request $request): Response
    {
        return $this->json(
            $this->builder->generate($this->authorizationChecker->getScope($request), $request->query->get('feature')),
            Response::HTTP_OK,
            [],
            ['groups' => ['filter:read']]
        );
    }
}

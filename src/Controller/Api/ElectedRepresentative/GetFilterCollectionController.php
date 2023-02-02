<?php

namespace App\Controller\Api\ElectedRepresentative;

use App\ElectedRepresentative\Filter\ElectedRepresentativeFiltersGenerator;
use App\Scope\ScopeGeneratorResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v3/elected_representatives/filters", name="app_elected_representatives_filters_get", methods={"GET"})
 *
 * @Security("is_granted('IS_FEATURE_GRANTED', 'elected_representative')")
 */
class GetFilterCollectionController extends AbstractController
{
    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly ElectedRepresentativeFiltersGenerator $builder
    ) {
    }

    public function __invoke(Request $request): Response
    {
        return $this->json(
            $this->builder->generate($this->scopeGeneratorResolver->generate()->getMainCode()),
            Response::HTTP_OK,
            [],
            ['groups' => ['filter:read']]
        );
    }
}

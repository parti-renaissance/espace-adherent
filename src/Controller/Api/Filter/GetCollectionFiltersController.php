<?php

namespace App\Controller\Api\Filter;

use App\JMEFilter\FiltersGenerator;
use App\Scope\ScopeGeneratorResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN')]
#[Route(path: '/v3/filters', name: 'app_collection_filters_get', methods: ['GET'])]
class GetCollectionFiltersController extends AbstractController
{
    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly FiltersGenerator $builder,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$feature = $request->query->get('feature')) {
            throw new BadRequestHttpException('Parameter "feature" is missing or empty');
        }

        return $this->json(
            $this->builder->generate($this->scopeGeneratorResolver->generate()->getMainCode(), $feature),
            Response::HTTP_OK,
            [],
            ['groups' => ['filter:read']]
        );
    }
}

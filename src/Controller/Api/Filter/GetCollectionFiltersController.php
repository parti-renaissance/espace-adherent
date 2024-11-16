<?php

namespace App\Controller\Api\Filter;

use App\JMEFilter\FiltersGenerator;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/v3/filters', name: 'app_collection_filters_get', methods: ['GET'])]
#[Security("is_granted('REQUEST_SCOPE_GRANTED', ['contacts', 'messages'])")]
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

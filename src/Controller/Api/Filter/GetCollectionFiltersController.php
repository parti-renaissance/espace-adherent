<?php

namespace App\Controller\Api\Filter;

use App\Filter\FiltersGenerator;
use App\Scope\ScopeGeneratorResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v3/filters", name="app_collection_filters_get", methods={"GET"})
 *
 * @Security("is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', ['contacts', 'messages', 'elected_representative'])")
 */
class GetCollectionFiltersController extends AbstractController
{
    private ScopeGeneratorResolver $scopeGeneratorResolver;
    private FiltersGenerator $builder;

    public function __construct(ScopeGeneratorResolver $scopeGeneratorResolver, FiltersGenerator $builder)
    {
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
        $this->builder = $builder;
    }

    public function __invoke(Request $request): Response
    {
        return $this->json(
            $this->builder->generate(
                $this->scopeGeneratorResolver->generate()->getMainCode(),
                $request->query->get('feature')
            ),
            Response::HTTP_OK,
            [],
            ['groups' => ['filter:read']]
        );
    }
}

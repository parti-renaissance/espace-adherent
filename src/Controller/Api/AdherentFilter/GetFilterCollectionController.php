<?php

namespace App\Controller\Api\AdherentFilter;

use App\AdherentFilter\AdherentFiltersGenerator;
use App\Scope\ScopeGeneratorResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v3/adherents/filters", name="app_adherents_filters_get", methods={"GET"})
 *
 * @Security("is_granted('IS_FEATURE_GRANTED', ['contacts', 'messages'])")
 */
class GetFilterCollectionController extends AbstractController
{
    private ScopeGeneratorResolver $scopeGeneratorResolver;
    private AdherentFiltersGenerator $builder;

    public function __construct(ScopeGeneratorResolver $scopeGeneratorResolver, AdherentFiltersGenerator $builder)
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

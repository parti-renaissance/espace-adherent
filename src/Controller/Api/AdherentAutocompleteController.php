<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Adherent\AdherentAutocompleteFilter;
use App\Repository\AdherentRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', ['team', 'my_team', 'committee'])"))]
#[Route(path: '/v3/adherents/autocomplete', name: 'api_adherent_autocomplete', methods: ['GET'])]
class AdherentAutocompleteController extends AbstractController
{
    public function __invoke(
        Request $request,
        AdherentRepository $repository,
        ScopeGeneratorResolver $scopeGeneratorResolver,
        DenormalizerInterface $denormalizer,
    ): JsonResponse {
        $zones = [];
        if ($scope = $scopeGeneratorResolver->generate()) {
            $zones = $scope->getZones();
        }

        $filter = new AdherentAutocompleteFilter($zones, $scope ? $scope->getCommitteeUuids() : []);

        $denormalizer->denormalize($request->query->all(), AdherentAutocompleteFilter::class, null, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $filter,
            AbstractNormalizer::GROUPS => ['filter_write'],
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ]);

        $maxResult = $request->query->getInt('max_result', 10);

        return $this->json(
            $repository->findAdherentByAutocompletion($filter, min($maxResult, 100)),
            Response::HTTP_OK,
            [],
            ['groups' => ['adherent_autocomplete']]
        );
    }
}

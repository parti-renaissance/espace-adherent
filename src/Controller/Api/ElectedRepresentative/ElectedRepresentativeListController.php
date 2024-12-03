<?php

namespace App\Controller\Api\ElectedRepresentative;

use App\ElectedRepresentative\Filter\ListFilter;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\Scope\ScopeGeneratorResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[Route(path: '/v3/elected_representatives', name: 'app_elected_representatives_list_get', methods: ['GET'])]
#[Security("is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative')")]
class ElectedRepresentativeListController extends AbstractController
{
    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly ElectedRepresentativeRepository $repository,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $scope = $this->scopeGeneratorResolver->generate();
        $filter = new ListFilter($scope->getZones(), $this->getUser());

        $this->denormalizer->denormalize($request->query->all(), ListFilter::class, null, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $filter,
            AbstractNormalizer::GROUPS => ['filter_write'],
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ]);

        $electedRepresentatives = $this->repository->searchByFilter(
            $filter,
            $request->query->getInt('page', 1),
            $request->query->getInt('page_size', 100)
        );

        return $this->json(
            $electedRepresentatives,
            Response::HTTP_OK,
            [],
            ['groups' => ['elected_representative_list']],
        );
    }
}

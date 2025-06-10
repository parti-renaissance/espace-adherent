<?php

namespace App\Controller\Api\Event;

use App\Event\Request\CountInvitationsRequest;
use App\Repository\AdherentRepository;
use App\Scope\Scope;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class CountInvitationsController extends AbstractController
{
    public function __invoke(ScopeGeneratorResolver $scopeGeneratorResolver, Request $request, SerializerInterface $serializer, AdherentRepository $adherentRepository): array
    {
        /** @var Scope $scope */
        $scope = $scopeGeneratorResolver->generate();

        return [
            'count' => $adherentRepository->countInvitations($serializer->deserialize($request->getContent(), CountInvitationsRequest::class, 'json', [
                AbstractNormalizer::OBJECT_TO_POPULATE => new CountInvitationsRequest(
                    $scope->getZones(),
                    $scope->getCommitteeUuids(),
                    $scope->getAgoraUuids()
                ),
            ])),
        ];
    }
}

<?php

namespace App\Controller\Api\AdherentMessage;

use App\Normalizer\ImageExposeNormalizer;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GetAvailableSendersController extends AbstractController
{
    public function __invoke(ScopeGeneratorResolver $resolver, NormalizerInterface $normalizer): Response
    {
        if (!$scope = $resolver->generate()) {
            return $this->json([]);
        }

        $user = $normalizer->normalize($scope->getMainUser(), context: [
            'groups' => ['adherent_message_sender', ImageExposeNormalizer::NORMALIZATION_GROUP],
        ]);

        return $this->json([array_merge($user, [
            'instance' => $scope->getScopeInstance(),
            'role' => $scope->getMainRoleName(),
            'zone' => implode(', ', $scope->getZoneNames()) ?: null,
            'theme' => $scope->getAttribute('theme'),
        ])]);
    }
}

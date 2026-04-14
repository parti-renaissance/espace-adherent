<?php

declare(strict_types=1);

namespace App\Controller\Renaissance;

use App\OAuth\Oidc\OidcDiscoveryMetadataBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/.well-known/openid-configuration',
    name: 'app_oidc_discovery',
    methods: ['GET'],
)]
class OidcDiscoveryController extends AbstractController
{
    public function __invoke(OidcDiscoveryMetadataBuilder $metadataBuilder): JsonResponse
    {
        return new JsonResponse($metadataBuilder->build());
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\OAuth;

use App\OAuth\Model\Scope;
use App\OAuth\Oidc\JwkSetProvider;
use App\Repository\AdherentRepository;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Nyholm\Psr7\Response;
use OpenIDConnectServer\ClaimExtractor;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class OidcController extends AbstractController
{
    public function __construct(
        private readonly JwkSetProvider $jwkSetProvider,
        private readonly ClaimExtractor $claimExtractor,
        private readonly ResourceServer $resourceServer,
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    #[Route(path: '/jwks', name: 'app_oidc_jwks', methods: ['GET'])]
    public function jwksAction(): JsonResponse
    {
        return new JsonResponse($this->jwkSetProvider->getJwkSet());
    }

    #[Route(path: '/userinfo', name: 'app_oidc_userinfo', methods: ['GET', 'POST'])]
    public function userinfoAction(Request $request): JsonResponse
    {
        try {
            $validatedRequest = $this->resourceServer->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $e) {
            return new JsonResponse(
                (string) $e->generateHttpResponse(new Response())->getBody(),
                $e->getHttpStatusCode(),
                [],
                true,
            );
        }

        $scopes = (array) $validatedRequest->getAttribute('oauth_scopes', []);

        if (!\in_array(Scope::OPENID, $scopes, true)) {
            return new JsonResponse(
                [
                    'error' => 'insufficient_scope',
                    'error_description' => 'The openid scope is required to access userinfo',
                ],
                403,
            );
        }

        $adherentUuid = $validatedRequest->getAttribute('oauth_user_id');
        if (!\is_string($adherentUuid)) {
            return new JsonResponse(['error' => 'invalid_token'], 401);
        }

        $adherent = $this->adherentRepository->findOneByUuid($adherentUuid);

        if (null === $adherent) {
            return new JsonResponse(['error' => 'invalid_token'], 401);
        }

        return new JsonResponse(
            ['sub' => $adherent->getIdentifier()]
            + $this->claimExtractor->extract($scopes, $adherent->getClaims())
        );
    }
}

<?php

namespace App\Controller\OAuth;

use App\Form\ConfirmActionType;
use App\OAuth\OAuthAuthorizationManager;
use App\Repository\OAuth\ClientRepository;
use App\Security\Voter\OAuthClientVoter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\RegisteredClaims;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OAuthServerController extends AbstractController
{
    public function __construct(
        private readonly AuthorizationServer $authorizationServer,
        private readonly HttpFoundationFactoryInterface $httpFoundationFactory,
    ) {
    }

    #[IsGranted(new Expression("is_granted('IS_AUTHENTICATED_REMEMBERED') and not is_granted('ROLE_ADMIN_DASHBOARD')"))]
    #[Route(path: '/auth', name: 'app_front_oauth_authorize', methods: ['GET', 'POST'])]
    public function authorizeAction(Request $request, ClientRepository $repository, OAuthAuthorizationManager $manager)
    {
        try {
            $user = $this->getUser();
            $authRequest = $this->authorizationServer->validateAuthorizationRequest($request);
            $authRequest->setUser($user->getOAuthUser());

            $client = $repository->findClientByUuid(Uuid::fromString($authRequest->getClient()->getIdentifier()));

            $this->denyAccessUnlessGranted(OAuthClientVoter::PERMISSION, $client);

            $form = $this
                ->createForm(ConfirmActionType::class)
                ->handleRequest($this->httpFoundationFactory->createRequest($request))
            ;

            $authorizationApproved = $manager->isAuthorized($user, $client, $authRequest->getScopes());

            if ($authorizationApproved || ($form->isSubmitted() && $form->isValid())) {
                $authorizationApproved = $form->get('allow')->isClicked() || $authorizationApproved;
                $authRequest->setAuthorizationApproved($authorizationApproved);

                if ($authorizationApproved) {
                    $manager->record($user, $client, $authRequest->getScopes());
                }

                $response = $this->authorizationServer->completeAuthorizationRequest($authRequest, new Response());

                if ($this->isGranted('IS_IMPERSONATOR') && $response->hasHeader('location')) {
                    $currentLocation = $response->getHeaderLine('location');

                    $response = $response->withHeader('location', $currentLocation.(!str_contains($currentLocation, '?') ? '?' : '&').'_switch_user=true');
                }

                return $response;
            }
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse(new Response());
        }

        return $this->render('oauth/authorize.html.twig', [
            'authorization_form' => $form->createView(),
            'client' => $client,
            'scopes' => $authRequest->getScopes(),
        ]);
    }

    #[Route(path: '/token', name: 'app_front_oauth_get_access_token', methods: ['POST'])]
    public function getAccessTokenAction(Request $request)
    {
        $response = new Response();

        try {
            return $this->authorizationServer->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    }

    #[Route(path: '/tokeninfo', name: 'app_front_oauth_get_access_token_info', methods: ['GET'])]
    public function getAccessTokenInfo(Request $request, ResourceServer $resourceServer, ClientRepository $repository)
    {
        $accessToken = $request->getQueryParams()['access_token'] ?? null;

        if (!$accessToken) {
            return new JsonResponse(['message' => 'No access_token provided'], 400);
        }

        try {
            $oauthRequest = $resourceServer->validateAuthenticatedRequest($request->withAddedHeader('Authorization', 'Bearer '.$accessToken));
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse(new Response());
        }

        $accessTokenObject = (new Parser(new JoseEncoder()))->parse($accessToken);
        $client = $repository->findClientByUuid(Uuid::fromString(current($accessTokenObject->claims()->get(RegisteredClaims::AUDIENCE))));

        return new JsonResponse([
            'token_type' => 'Bearer',
            'expires_in' => $accessTokenObject->claims()->get(RegisteredClaims::EXPIRATION_TIME)->getTimestamp() - time(),
            'access_token' => $accessToken,
            'grant_types' => $client->getAllowedGrantTypes(),
            'scopes' => $oauthRequest->getAttribute('oauth_scopes'),
        ]);
    }
}

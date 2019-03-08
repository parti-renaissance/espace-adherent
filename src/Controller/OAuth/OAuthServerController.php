<?php

namespace AppBundle\Controller\OAuth;

use AppBundle\Form\ConfirmActionType;
use AppBundle\OAuth\OAuthAuthorizationManager;
use AppBundle\Repository\OAuth\ClientRepository;
use Lcobucci\JWT\Parser;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Zend\Diactoros\Response;

class OAuthServerController extends Controller
{
    private $authorizationServer;
    private $httpFoundationFactory;

    public function __construct(AuthorizationServer $authorizationServer, HttpFoundationFactory $httpFoundationFactory)
    {
        $this->authorizationServer = $authorizationServer;
        $this->httpFoundationFactory = $httpFoundationFactory;
    }

    /**
     * @Route("/auth", name="app_front_oauth_authorize")
     * @Method("GET|POST")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY') and not is_granted('ROLE_ADMIN_DASHBOARD')")
     */
    public function authorizeAction(Request $request, ClientRepository $repository, OAuthAuthorizationManager $manager)
    {
        try {
            $user = $this->getUser();
            $authRequest = $this->authorizationServer->validateAuthorizationRequest($request);
            $authRequest->setUser($user->getOAuthUser());

            $client = $repository->findClientByUuid(Uuid::fromString($authRequest->getClient()->getIdentifier()));
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

                return $this->authorizationServer->completeAuthorizationRequest($authRequest, new Response());
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

    /**
     * @Route("/token", name="app_front_oauth_get_access_token")
     * @Method("POST")
     */
    public function getAccessTokenAction(Request $request)
    {
        $response = new Response();

        try {
            return $this->authorizationServer->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    }

    /**
     * @Route("/tokeninfo", name="app_front_oauth_get_access_token_info")
     * @Method("GET")
     */
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

        $accessTokenObject = (new Parser())->parse($accessToken);
        $client = $repository->findClientByUuid(Uuid::fromString($accessTokenObject->getClaim('aud')));

        return new JsonResponse([
            'token_type' => 'Bearer',
            'expires_in' => $accessTokenObject->getClaim('exp') - time(),
            'access_token' => $accessToken,
            'grant_types' => $client->getAllowedGrantTypes(),
            'scopes' => $oauthRequest->getAttribute('oauth_scopes'),
        ]);
    }
}

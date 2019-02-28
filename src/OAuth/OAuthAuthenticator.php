<?php

namespace AppBundle\OAuth;

use AppBundle\OAuth\Model\ApiUser;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Security\Exception\BadCredentialsException;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class OAuthAuthenticator extends AbstractGuardAuthenticator
{
    private $resourceServer;
    private $diactorosFactory;
    private $adherentRepository;

    public function __construct(
        ResourceServer $resourceServer,
        HttpMessageFactoryInterface $diactorosFactory,
        AdherentRepository $adherentRepository
    ) {
        $this->resourceServer = $resourceServer;
        $this->diactorosFactory = $diactorosFactory;
        $this->adherentRepository = $adherentRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'message' => 'Authentication Required.',
        ];

        if ($authException) {
            $data['message'] = $authException->getMessage();
        }

        return new JsonResponse($data, 401);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        $psrRequest = $this->diactorosFactory->createRequest($request);

        try {
            $psrRequest = $this->resourceServer->validateAuthenticatedRequest($psrRequest);
        } catch (OAuthServerException $e) {
            throw new AuthenticationException($e->getMessage(), Response::HTTP_UNAUTHORIZED, $e);
        }

        return [
            'oauth_access_token_id' => $psrRequest->getAttribute('oauth_access_token_id'),
            'oauth_client_id' => $psrRequest->getAttribute('oauth_client_id'),
            'oauth_user_id' => $psrRequest->getAttribute('oauth_user_id'),
            'oauth_scopes' => $psrRequest->getAttribute('oauth_scopes'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $roles = array_map(
            function ($scope) {return 'ROLE_OAUTH_SCOPE_'.mb_strtoupper($scope); },
            $credentials['oauth_scopes']
        );

        // If user identifier is empty, it just means that the token is associated to an OAuth Client for
        // machine-to-machine communication only
        if (!$credentials['oauth_user_id']) {
            return new ApiUser($credentials['oauth_client_id'], $roles);
        }

        if (!$user = $this->adherentRepository->findByUuid(Uuid::fromString($credentials['oauth_user_id']))) {
            $e = new UsernameNotFoundException(sprintf('Unable to find User by UUID "%s".', $credentials['oauth_user_id']));

            throw new BadCredentialsException('Invalid credentials.', 0, $e);
        }

        $user->addRoles($roles);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->start($request, $exception);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}

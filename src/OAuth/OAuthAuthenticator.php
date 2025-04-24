<?php

namespace App\OAuth;

use App\Entity\Adherent;
use App\OAuth\Model\ClientApiUser;
use App\OAuth\Model\DeviceApiUser;
use App\OAuth\Model\Scope;
use App\Repository\AdherentRepository;
use App\Repository\DeviceRepository;
use App\Repository\OAuth\AccessTokenRepository;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class OAuthAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly ResourceServer $resourceServer,
        private readonly HttpMessageFactoryInterface $httpMessageFactory,
        private readonly AdherentRepository $adherentRepository,
        private readonly DeviceRepository $deviceRepository,
        private readonly AccessTokenRepository $accessTokenRepository,
    ) {
    }

    public function getCredentials(Request $request): array
    {
        $psrRequest = $this->httpMessageFactory->createRequest($request);

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
            'oauth_device_id' => $psrRequest->getAttribute('oauth_device_id'),
            'oauth_client_code' => $psrRequest->getAttribute('oauth_client_code'),
        ];
    }

    public function getUser($credentials): Adherent|DeviceApiUser|ClientApiUser
    {
        $roles = array_map([Scope::class, 'generateRole'], $credentials['oauth_scopes']);

        // If user identifier is empty, it just means that the token is associated to an OAuth Client for
        // machine-to-machine communication only
        if (!$credentials['oauth_user_id']) {
            if ($deviceUuid = $credentials['oauth_device_id']) {
                if (!$device = $this->deviceRepository->findOneByDeviceUuid($deviceUuid)) {
                    throw new BadCredentialsException('Invalid credentials.', 0);
                }

                return new DeviceApiUser($credentials['oauth_client_id'], $roles, $device);
            }

            return new ClientApiUser($credentials['oauth_client_id'], $roles);
        }

        if (!$user = $this->adherentRepository->loadUserByUuid(Uuid::fromString($credentials['oauth_user_id']))) {
            $e = new UserNotFoundException(\sprintf('Unable to find User by UUID "%s".', $credentials['oauth_user_id']));

            throw new BadCredentialsException('Invalid credentials.', 0, $e);
        }

        $user->addRoles($roles);
        $user->setAuthAppCode($credentials['oauth_client_code'] ?? null);

        if (($accessToken = $this->accessTokenRepository->findAccessTokenByIdentifier($credentials['oauth_access_token_id'])) && $accessToken->appSession) {
            $user->currentAppSession = $accessToken->appSession;
        }

        return $user;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if (($user = $token->getUser()) instanceof Adherent) {
            $user->setAuthAppVersion($request->headers->get('X-App-Version'));
        }

        return null;
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $user = $this->getUser($this->getCredentials($request));

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier(), fn () => $user));
    }

    public function supports(Request $request): ?bool
    {
        return true;
    }
}

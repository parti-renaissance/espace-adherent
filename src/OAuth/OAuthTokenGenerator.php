<?php

declare(strict_types=1);

namespace App\OAuth;

use App\OAuth\Model\GrantTypeEnum;
use App\Repository\OAuth\ClientRepository;
use League\OAuth2\Server\AuthorizationServer;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthTokenGenerator
{
    private $clientRepository;
    private $authorizationServer;
    /**
     * @var HttpMessageFactoryInterface
     */
    private $httpMessageFactory;

    public function __construct(
        ClientRepository $clientRepository,
        AuthorizationServer $authorizationServer,
        HttpMessageFactoryInterface $httpMessageFactory,
    ) {
        $this->clientRepository = $clientRepository;
        $this->authorizationServer = $authorizationServer;
        $this->httpMessageFactory = $httpMessageFactory;
    }

    public function generate(
        Request $request,
        UserInterface $user,
        string $clientId,
        string $password,
    ): ?ResponseInterface {
        if (!Uuid::isValid($clientId)) {
            return null;
        }

        $client = $this->clientRepository->findOneByUuid($clientId);
        if (null === $client) {
            return null;
        }
        $oauthRequest = new Request(
            server: $request->server->all(),
            content: json_encode([
                'client_id' => $clientId,
                'client_secret' => $client->getSecret(),
                'grant_type' => GrantTypeEnum::PASSWORD,
                'username' => $user->getUserIdentifier(),
                'password' => $password,
            ])
        );

        try {
            return $this->authorizationServer->respondToAccessTokenRequest(
                $this->httpMessageFactory->createRequest($oauthRequest),
                new Response()
            );
        } catch (\Throwable $exception) {
        }

        return null;
    }
}

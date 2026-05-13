<?php

declare(strict_types=1);

namespace Tests\App\Controller;

use App\Entity\OAuth\AccessToken as EntityAccessToken;
use App\OAuth\DirectTokenIssuer;
use App\OAuth\Model\AccessToken;
use App\OAuth\Model\Client;
use App\OAuth\Model\Scope;
use App\Repository\AdherentRepository;
use App\Repository\OAuth\ClientRepository;
use League\OAuth2\Server\CryptKey;

trait ApiControllerTestTrait
{
    protected function getAccessToken(
        string $clientUuid,
        ?string $scope,
        ?string $username = null,
    ): ?string {
        $container = $this->client->getContainer();

        /** @var ClientRepository $clientRepository */
        $clientRepository = $container->get(ClientRepository::class);
        $client = $clientRepository->findOneByUuid($clientUuid);
        if (null === $client) {
            throw new \RuntimeException(\sprintf('Unknown test OAuth client "%s"', $clientUuid));
        }

        $user = null;
        if (null !== $username) {
            /** @var AdherentRepository $adherentRepository */
            $adherentRepository = $container->get(AdherentRepository::class);
            $user = $adherentRepository->findOneByEmail($username);
            if (null === $user) {
                throw new \RuntimeException(\sprintf('Unknown test adherent "%s"', $username));
            }
        }

        $scopes = null !== $scope ? [new Scope($scope)] : [];

        /** @var DirectTokenIssuer $issuer */
        $issuer = $container->get(DirectTokenIssuer::class);
        $response = $issuer->issue($client, $user, $scopes);
        $body = (string) $response->getBody();

        return json_decode($body, true, 512, \JSON_THROW_ON_ERROR)['access_token'] ?? null;
    }

    protected function assertEachJsonItemContainsKey($key, $json, array $excluding = [])
    {
        $data = json_decode($json, true);

        foreach ($data as $k => $item) {
            if (\in_array($k, $excluding, true)) {
                continue;
            }
            $this->assertArrayHasKey($key, $item, 'Item '.$k.' of JSON payload does not have '.$key.' key');
        }
    }

    protected function getJwtAccessTokenByIdentifier(string $identifier, CryptKey $privateKey): string
    {
        /** @var EntityAccessToken $accessToken */
        $accessToken = $this
            ->get('doctrine')
            ->getManager()
            ->getRepository(EntityAccessToken::class)
            ->findAccessTokenByIdentifier($identifier)
        ;

        $client = new Client($accessToken->getClientIdentifier(), []);

        $token = new AccessToken();
        $token->setClient($client);
        $token->setIdentifier($identifier);
        $token->setExpiryDateTime($accessToken->getExpiryDateTime());
        if (null !== $userIdentifier = $accessToken->getUserIdentifier()) {
            $token->setUserIdentifier($userIdentifier);
        }
        $token->setPrivateKey($privateKey);

        foreach ($accessToken->getScopes() as $scope) {
            $token->addScope(new Scope($scope));
        }

        return $token->toString();
    }
}

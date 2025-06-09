<?php

namespace App\OAuth\Store;

use App\Entity\OAuth\Client as EntityClient;
use App\OAuth\Model\Client as InMemoryClient;
use App\OAuth\Model\GrantTypeEnum;
use App\Repository\OAuth\ClientRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface as OAuthClientRepositoryInterface;
use Ramsey\Uuid\Uuid;

class ClientStore implements OAuthClientRepositoryInterface
{
    private $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * @param string $clientIdentifier
     *
     * @return ClientEntityInterface|null
     */
    public function getClientEntity($clientIdentifier)
    {
        if (!$client = $this->findClientEntity($clientIdentifier)) {
            return null;
        }

        $oAuthClient = new InMemoryClient($client->getUuid(), $client->getSupportedScopes());
        $oAuthClient->setName($client->getName());
        $oAuthClient->setRedirectUris($client->getRedirectUris());

        return $oAuthClient;
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        if (!$client = $this->findClientEntity($clientIdentifier)) {
            return false;
        }

        if (
            (
                null !== $clientSecret
                || !\in_array($grantType, [GrantTypeEnum::AUTHORIZATION_CODE, GrantTypeEnum::REFRESH_TOKEN])
            )
            && !$client->verifySecret($clientSecret)) {
            return false;
        }

        if (!$client->isAllowedGrantType($grantType)) {
            return false;
        }

        return true;
    }

    private function findClientEntity(string $identifier): ?EntityClient
    {
        if (!Uuid::isValid($identifier)) {
            return null;
        }

        return $this->clientRepository->findOneByUuid($identifier);
    }
}

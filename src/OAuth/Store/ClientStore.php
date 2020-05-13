<?php

namespace App\OAuth\Store;

use App\OAuth\Model\Client as InMemoryClient;
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
     * @param string      $clientIdentifier
     * @param string      $grantType
     * @param string|null $clientSecret
     * @param bool        $mustValidateSecret
     *
     * @return ClientEntityInterface|null
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        if (!Uuid::isValid($clientIdentifier)) {
            return null;
        }

        if (!$client = $this->clientRepository->findClientByUuid(Uuid::fromString($clientIdentifier))) {
            return null;
        }

        if ($mustValidateSecret && !$client->verifySecret($clientSecret)) {
            return null;
        }

        if (!$client->isAllowedGrantType($grantType)) {
            return null;
        }

        $oAuthClient = new InMemoryClient($client->getUuid(), $client->getSupportedScopes());
        $oAuthClient->setName($client->getName());
        $oAuthClient->setRedirectUris($client->getRedirectUris());

        return $oAuthClient;
    }
}

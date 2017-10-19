<?php

namespace AppBundle\Membership;

use AppBundle\Api\AccessTokenManager;
use AppBundle\Exception\ApiException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;

class AdherentAccountProvider
{
    private $httpClient;
    private $accessTokenManager;

    public function __construct(
        AccessTokenManager $accessTokenManager,
        ClientInterface $httpClient
    ) {
        $this->accessTokenManager = $accessTokenManager;
        $this->httpClient = $httpClient;
    }

    public function getUser(string $iri): AdherentAccountData
    {
        try {
            $response = $this->httpClient->request('GET', $iri, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => sprintf('Bearer %s', $this->accessTokenManager->getAccessToken()),
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new ApiException('Unable to retrieve user account data.', $e);
        }

        if (200 !== $response->getStatusCode()) {
            throw new ApiException('Unable to retrieve user account data.');
        }

        return $this->getSerializer()->deserialize(
            $response->getBody()->getContents(),
            AdherentAccountData::class,
            'json'
        );
    }

    private function getSerializer(): SerializerInterface
    {
        return SerializerBuilder::create()
            ->setPropertyNamingStrategy(new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy()))
            ->build();
    }
}

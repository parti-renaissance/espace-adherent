<?php

namespace AppBundle\Membership;

use GuzzleHttp\ClientInterface;
use JMS\Serializer\SerializerInterface;

class AdherentAccountProvider
{
    private $api;
    private $apiTokenManager;
    private $serializer;

    public function __construct(
        SerializerInterface $serializer,
        OAuthTokenManager $apiTokenManager,
        ClientInterface $api
    ) {
        $this->serializer = $serializer;
        $this->apiTokenManager = $apiTokenManager;
        $this->api = $api;
    }

    public function getUser(string $iri): AdherentAccountData
    {
        $response = $this->api->request('GET', $iri, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => sprintf(
                    'Bearer %s',
                    $this->apiTokenManager->getAccessToken()
                ),
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Not enable to retrieve user information.');
        }

        return $this->serializer->deserialize(
            $response->getBody()->getContents(),
            AdherentAccountData::class,
            'json'
        );
    }
}

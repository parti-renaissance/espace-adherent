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
use Psr\Log\LoggerInterface;

class AdherentAccountProvider
{
    private $httpClient;
    private $accessTokenManager;
    private $logger;

    public function __construct(
        AccessTokenManager $accessTokenManager,
        ClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->accessTokenManager = $accessTokenManager;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
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
            $this->logger->info('Unable to retrieve user account data.', ['exception' => $e]);

            throw new ApiException('Unable to retrieve user account data.', $e);
        }

        if (200 !== $response->getStatusCode()) {
            $this->logger->info('Unable to retrieve user account data.', ['response' => $response]);

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

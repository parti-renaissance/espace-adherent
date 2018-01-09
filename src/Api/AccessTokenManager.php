<?php

namespace AppBundle\Api;

use AppBundle\Exception\ApiException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\CacheItemPoolInterface;

class AccessTokenManager
{
    const TOKEN_CACHE_KEY = '_api.access_token';

    private $httpClient;
    private $cache;
    private $clientId;
    private $clientSecret;

    public function __construct(
        ClientInterface $httpClient,
        CacheItemPoolInterface $cache,
        string $clientId,
        string $clientSecret
    ) {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function getAccessToken(): string
    {
        $item = $this->cache->getItem(self::TOKEN_CACHE_KEY);

        if (!$item->isHit()) {
            $token = $this->requestAccessToken();
            $item->set($token['access_token']);
            $item->expiresAfter($token['expires_in']);

            $this->cache->save($item);
        }

        return $item->get();
    }

    private function requestAccessToken(): array
    {
        try {
            $response = $this->httpClient->request('POST', '/oauth/v2/token', [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                    //'scope' => '',
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json',
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new ApiException('Unable to get access token from API.', $e);
        }

        if (200 !== $response->getStatusCode()) {
            throw new ApiException('Unable to get access token from API');
        }

        $data = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        if (empty($data['access_token'])) {
            throw new ApiException('Response does not contain access token.');
        }

        return $data;
    }
}

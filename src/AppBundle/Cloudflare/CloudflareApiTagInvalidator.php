<?php

namespace AppBundle\Cloudflare;

use GuzzleHttp\ClientInterface;

class CloudflareApiTagInvalidator implements CloudflareTagInvalidatorInterface
{
    private $client;
    private $apiEmail;
    private $apiKey;

    public function __construct(ClientInterface $client, string $apiEmail, string $apiKey)
    {
        $this->client = $client;
        $this->apiEmail = $apiEmail;
        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags)
    {
        if (!empty($this->apiEmail) && !empty($this->apiKey)) {
            $this->client->request('DELETE', 'purge_cache', [
                'headers' => [
                    'X-Auth-Email' => $this->apiEmail,
                    'X-Auth-Key' => $this->apiKey,
                ],
                'body' => \GuzzleHttp\json_encode([
                    'tags' => $tags,
                ]),
            ]);
        }
    }
}

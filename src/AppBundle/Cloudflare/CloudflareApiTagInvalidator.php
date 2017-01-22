<?php

namespace AppBundle\Cloudflare;

use GuzzleHttp\ClientInterface;

class CloudflareApiTagInvalidator implements CloudflareTagInvalidatorInterface
{
    private $client;
    private $zoneId;
    private $apiEmail;
    private $apiKey;

    public function __construct(ClientInterface $client, string $zoneId, string $apiEmail, string $apiKey)
    {
        $this->client = $client;
        $this->zoneId = $zoneId;
        $this->apiEmail = $apiEmail;
        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags)
    {
        if (!empty($this->zoneId) && !empty($this->apiEmail) && !empty($this->apiKey)) {
            $this->client->request('DELETE', 'zones/'.$this->zoneId.'/purge_cache', [
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

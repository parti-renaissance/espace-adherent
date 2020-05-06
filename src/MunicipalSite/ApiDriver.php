<?php

namespace App\MunicipalSite;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ApiDriver implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function isMunicipalSiteEnabled(string $inseeCode): bool
    {
        try {
            $response = $this->client->request(
                'GET',
                '/api/municipal_sites',
                ['query' => ['cityInseeCode' => $inseeCode]]
            );
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage(), ['insee_code' => $inseeCode]);

            return false;
        }

        $data = \json_decode($response->getBody(), true);

        if (!empty($data['items'])) {
            foreach ($data['items'] as $site) {
                if (isset($site['city_insee_code']) && $site['city_insee_code'] === $inseeCode) {
                    return true;
                }
            }
        }

        return false;
    }
}

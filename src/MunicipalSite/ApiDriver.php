<?php

namespace App\MunicipalSite;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiDriver implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function isMunicipalSiteEnabled(string $inseeCode): bool
    {
        try {
            $data = $this->client->request(
                'GET',
                '/api/municipal_sites',
                ['query' => ['cityInseeCode' => $inseeCode]]
            )->toArray();
        } catch (ExceptionInterface $e) {
            $this->logger->error($e->getMessage(), ['insee_code' => $inseeCode]);

            return false;
        }

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

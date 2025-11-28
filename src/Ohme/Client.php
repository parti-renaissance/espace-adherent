<?php

declare(strict_types=1);

namespace App\Ohme;

use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client implements ClientInterface
{
    private readonly LimiterInterface $limiter;

    public function __construct(
        private readonly HttpClientInterface $ohmeClient,
        private readonly RateLimiterFactory $ohmeApiRequestLimiter,
    ) {
        $this->limiter = $this->ohmeApiRequestLimiter->create('ohme_api_request');
    }

    public function updateContact(string $contactId, array $data): array
    {
        $options = [
            'body' => $data,
        ];

        return $this->request('PUT', "contacts/$contactId", $options);
    }

    public function getContacts(int $limit = 100, int $offset = 0, array $options = []): array
    {
        $options = [
            'query' => array_merge($options, [
                'limit' => $limit,
                'offset' => $offset,
            ]),
        ];

        return $this->request('GET', 'contacts', $options);
    }

    public function getPayments(int $limit = 100, int $offset = 0, array $options = []): array
    {
        $options = [
            'query' => array_merge($options, [
                'limit' => $limit,
                'offset' => $offset,
            ]),
        ];

        return $this->request('GET', 'payments', $options);
    }

    private function request(string $method, string $url, array $options): array
    {
        $this->limiter->reserve()->wait();
        $this->limiter->consume();

        return $this->ohmeClient->request($method, $url, $options)->toArray();
    }
}

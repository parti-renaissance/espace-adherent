<?php

namespace App\Ohme;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client implements ClientInterface
{
    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    public function getContacts(int $limit = 100, int $offset = 0, array $options = []): array
    {
        $options = [
            'query' => array_merge($options, [
                'limit' => $limit,
                'offset' => $offset,
            ]),
        ];

        return $this->client->request('GET', 'contacts', $options)->toArray();
    }

    public function getPayments(int $limit = 100, int $offset = 0, array $options = []): array
    {
        $options = [
            'query' => array_merge($options, [
                'limit' => $limit,
                'offset' => $offset,
            ]),
        ];

        return $this->client->request('GET', 'payments', $options)->toArray();
    }
}

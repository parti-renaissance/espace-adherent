<?php

namespace App\Ohme;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $ohmeClientName,
        private readonly string $ohmeClientSecret
    ) {
    }

    public function getContacts(int $limit = 100, int $offset = 0, array $options = []): array
    {
        return $this->client->request('GET', 'contacts', array_merge($options, [
            'limit' => $limit,
            'offset' => $offset,
        ]))->toArray();
    }

    public function getPayments(int $limit = 100, int $offset = 0, array $options = []): array
    {
        return $this->client->request('GET', 'payments', array_merge($options, [
            'limit' => $limit,
            'offset' => $offset,
        ]))->toArray();
    }
}

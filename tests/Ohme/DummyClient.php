<?php

namespace Tests\App\Ohme;

use App\Ohme\ClientInterface;

class DummyClient implements ClientInterface
{
    public function getContacts(int $limit = 100, int $offset = 0, array $options = []): array
    {
        return [
            'status' => 200,
            'count' => 0,
            'data' => [],
        ];
    }

    public function getPayments(int $limit = 100, int $offset = 0, array $options = []): array
    {
        return [
            'status' => 200,
            'count' => 0,
            'data' => [],
        ];
    }
}

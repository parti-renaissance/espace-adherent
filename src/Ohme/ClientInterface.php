<?php

namespace App\Ohme;

interface ClientInterface
{
    public function getContacts(int $limit = 100, int $offset = 0, array $options = []): array;

    public function getPayments(int $limit = 100, int $offset = 0, array $options = []): array;
}

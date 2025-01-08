<?php

namespace App\Ohme;

interface ClientInterface
{
    public function updateContact(string $contactId, array $data): array;

    public function getContacts(int $limit = 100, int $offset = 0, array $options = []): array;

    public function getPayments(array $options = []): array;
}

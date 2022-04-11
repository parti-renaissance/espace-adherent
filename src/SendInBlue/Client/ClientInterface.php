<?php

namespace App\SendInBlue\Client;

interface ClientInterface
{
    public function synchronize(string $email, int $listId, array $attributes): void;

    public function delete(string $email): void;
}

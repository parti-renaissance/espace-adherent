<?php

namespace App\SendInBlue;

interface ClientInterface
{
    public function synchronize(string $email, int $listId, array $attributes): void;
}

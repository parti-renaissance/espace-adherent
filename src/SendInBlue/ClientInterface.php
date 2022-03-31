<?php

namespace App\SendInBlue;

interface ClientInterface
{
    public function synchronize(string $email, int $listId, array $attributes): void;

    public function delete(string $email): void;
}

<?php

namespace Tests\App\Test\SendInBlue;

use App\SendInBlue\ClientInterface;

class DummyClient implements ClientInterface
{
    public function synchronize(string $email, int $listId, array $attributes): void
    {
    }
}

<?php

namespace Tests\App\Test\SendInBlue;

use App\SendInBlue\ClientInterface;

class DummyClient implements ClientInterface
{
    private array $update = [];
    private array $delete = [];

    public function synchronize(string $email, int $listId, array $attributes): void
    {
        $this->update[] = [
            'email' => $email,
            'listId' => $listId,
            'attributes' => $attributes,
        ];
    }

    public function delete(string $email): void
    {
        $this->delete[] = $email;
    }

    public function getUpdateSchedule(): array
    {
        return $this->update;
    }

    public function getDeleteSchedule(): array
    {
        return $this->delete;
    }
}

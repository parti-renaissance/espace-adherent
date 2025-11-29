<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
class InvalidEmailAddress
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\Column]
    private string $emailHash;

    public function __construct(string $emailHash, ?UuidInterface $uuid = null)
    {
        $this->emailHash = $emailHash;
        $this->uuid = $uuid ?? Uuid::uuid4();
    }
}

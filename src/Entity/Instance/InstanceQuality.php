<?php

namespace App\Entity\Instance;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Instance\InstanceQualityRepository")
 */
class InstanceQuality
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     */
    private $code;

    /**
     * @var string[]
     *
     * @ORM\Column(type="simple_array")
     */
    private $scopes;

    public function __construct(string $code, array $scopes, UuidInterface $uuid = null)
    {
        $this->code = $code;
        $this->scopes = $scopes;
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function equals(self $quality): bool
    {
        return $this->id === $quality->getId();
    }
}

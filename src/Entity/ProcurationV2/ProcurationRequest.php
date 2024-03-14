<?php

namespace App\Entity\ProcurationV2;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityUTMTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="procuration_v2_initial_requests")
 * @ORM\Entity
 */
class ProcurationRequest
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityUTMTrait;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    public ?string $email = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function createForEmail(string $email): self
    {
        $object = new self();

        $object->email = $email;

        return $object;
    }
}

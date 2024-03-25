<?php

namespace App\Entity\ProcurationV2;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityUTMTrait;
use App\Procuration\V2\InitialRequestTypeEnum;
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

    /**
     * @ORM\Column(enumType=InitialRequestTypeEnum::class)
     *
     * @Assert\NotBlank
     */
    public ?InitialRequestTypeEnum $type = null;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    public ?string $clientIp = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function createForEmail(string $email, InitialRequestTypeEnum $type): self
    {
        $object = new self();

        $object->email = $email;
        $object->type = $type;

        return $object;
    }
}

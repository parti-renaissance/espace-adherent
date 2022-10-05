<?php

namespace App\Entity\Renaissance\Adhesion;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class AdherentRequest
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityPostAddressTrait;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    public ?string $firstName = null;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    public ?string $lastName = null;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    public ?string $email = null;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank
     */
    public ?int $amount = null;

    /**
     * @ORM\Column(type="uuid")
     */
    public UuidInterface $token;

    public function __construct()
    {
        $this->token = Uuid::uuid4();
    }
}

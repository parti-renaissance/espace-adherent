<?php

namespace App\Entity\Campus;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="campus_registration")
 */
class Registration
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(length=50)
     */
    public ?string $eventMakerUid = null;

    /**
     * @ORM\Column(length=50)
     */
    public ?string $eventMakerOrderUid = null;

    /**
     * @ORM\Column
     */
    public ?string $status = null;

    /**
     * @ORM\Column(type="datetime")
     */
    public ?\DateTimeInterface $registeredAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", inversedBy="campusRegistrations")
     * @ORM\JoinColumn(nullable=false)
     */
    public ?Adherent $adherent = null;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }
}

<?php

namespace App\Entity\Procuration;

use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="procuration_v2_rounds")
 * @ORM\Entity(repositoryClass="App\Repository\Procuration\RounRepository")
 */
class Round
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    public ?string $name = null;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     */
    public ?string $description = null;

    /**
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank
     */
    public ?\DateTimeInterface $date = null;

    /**
     * @ORM\ManyToOne(targetEntity=Election::class, inversedBy="rounds")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    public ?Election $election = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}

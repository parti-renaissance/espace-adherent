<?php

namespace App\Entity\ProcurationV2;

use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Procuration\RoundRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'procuration_v2_rounds')]
#[ORM\Entity(repositoryClass: RoundRepository::class)]
class Round
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    #[ORM\Column]
    public ?string $name = null;

    /**
     * @Assert\NotBlank
     */
    #[ORM\Column(type: 'text')]
    public ?string $description = null;

    /**
     * @Assert\NotBlank
     */
    #[ORM\Column(type: 'date')]
    public ?\DateTimeInterface $date = null;

    /**
     * @Assert\NotBlank
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Election::class, inversedBy: 'rounds')]
    public ?Election $election = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->election->name, $this->name);
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\ProcurationV2;

use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Procuration\RoundRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoundRepository::class)]
#[ORM\Index(columns: ['date'])]
#[ORM\Table(name: 'procuration_v2_rounds')]
class Round implements EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_matched_proxy', 'procuration_proxy_list', 'procuration_request_slot_read', 'procuration_proxy_slot_read'])]
    #[ORM\Column]
    public ?string $name = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    public ?string $description = null;

    #[Assert\NotBlank]
    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_matched_proxy', 'procuration_proxy_list', 'procuration_request_slot_read', 'procuration_proxy_slot_read'])]
    #[ORM\Column(type: 'date')]
    public ?\DateTimeInterface $date = null;

    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Election::class, inversedBy: 'rounds')]
    public ?Election $election = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString(): string
    {
        return \sprintf('%s - %s', $this->election->name, $this->name);
    }

    public function isUpcoming(): bool
    {
        return $this->date && $this->date > new \DateTime();
    }
}

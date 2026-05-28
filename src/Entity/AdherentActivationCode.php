<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AdherentActivationCodeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdherentActivationCodeRepository::class)]
#[ORM\Index(columns: ['adherent_id', 'value'])]
class AdherentActivationCode
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private Adherent $adherent;

    #[ORM\Column]
    public string $value;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $failedAttempts = 0;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $expiredAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $usedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $revokedAt = null;

    public static function create(
        Adherent $adherent,
        int $codeTtl,
        int $codeLength = 4,
    ): self {
        $code = new self();
        $code->adherent = $adherent;
        $code->value = static::generateValue($codeLength);
        $code->createdAt = new \DateTime();
        $code->expiredAt = new \DateTime('+'.$codeTtl.' min');

        return $code;
    }

    private static function generateValue(int $length = 4): string
    {
        if ($length < 1 || $length > 10) {
            throw new \InvalidArgumentException('Code length must be between 1 and 10.');
        }

        $code = '';
        for ($i = 0; $i < $length; ++$i) {
            $code .= random_int(0, 9);
        }

        return $code;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function isExpired(): bool
    {
        return $this->expiredAt < new \DateTime();
    }

    public function isRevoked(): bool
    {
        return null !== $this->revokedAt;
    }
}

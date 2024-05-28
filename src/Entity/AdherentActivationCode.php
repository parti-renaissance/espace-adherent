<?php

namespace App\Entity;

use App\Repository\AdherentActivationCodeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table]
#[ORM\Index(columns: ['adherent_id', 'value'])]
#[ORM\Entity(repositoryClass: AdherentActivationCodeRepository::class)]
class AdherentActivationCode
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private Adherent $adherent;

    #[ORM\Column]
    public string $value;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $expiredAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $usedAt = null;

    public static function create(Adherent $adherent, int $codeTtl): self
    {
        $code = new self();
        $code->adherent = $adherent;
        $code->value = static::generateValue();
        $code->createdAt = new \DateTime();
        $code->expiredAt = new \DateTime('+'.$codeTtl.' min');

        return $code;
    }

    private static function generateValue(): string
    {
        $numbers = range(0, 9);
        shuffle($numbers);

        return implode('', \array_slice($numbers, 0, 4));
    }

    public function isExpired(): bool
    {
        return $this->expiredAt < new \DateTime();
    }
}

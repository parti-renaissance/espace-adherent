<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentActivationCodeRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"adherent_id", "value"})})
 */
class AdherentActivationCode
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Adherent::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Adherent $adherent;

    /**
     * @ORM\Column
     */
    public string $value;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $expiredAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
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

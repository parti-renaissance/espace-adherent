<?php

namespace App\Entity;

use App\Adherent\Notification\NotificationTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentNotificationRepository")
 */
class AdherentNotification
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Adherent $adherent;

    /**
     * @ORM\Column(enumType=NotificationTypeEnum::class)
     */
    private string $type;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeInterface $createdAt;

    public function __construct(Adherent $adherent, string $type)
    {
        $this->adherent = $adherent;
        $this->type = $type;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}

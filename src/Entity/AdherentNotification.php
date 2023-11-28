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
    public ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public Adherent $adherent;

    /**
     * @ORM\Column(enumType=NotificationTypeEnum::class)
     */
    public NotificationTypeEnum $type;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    public \DateTimeInterface $createdAt;

    public function __construct(Adherent $adherent, NotificationTypeEnum $type)
    {
        $this->adherent = $adherent;
        $this->type = $type;
        $this->createdAt = new \DateTimeImmutable();
    }
}

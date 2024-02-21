<?php

namespace App\Entity\NationalEvent;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityNameSlugTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NationalEvent\NationalEventRepository")
 */
class NationalEvent
{
    use EntityIdentityTrait;
    use EntityNameSlugTrait;
    use EntityTimestampableTrait;

    /**
     * @Groups({"national_event_inscription:webhook"})
     *
     * @ORM\Column(type="datetime")
     */
    public ?\DateTime $startDate = null;

    /**
     * @Groups({"national_event_inscription:webhook"})
     *
     * @ORM\Column(type="datetime")
     */
    public ?\DateTime $endDate = null;

    /**
     * @ORM\Column(type="datetime")
     */
    public ?\DateTime $ticketStartDate = null;

    /**
     * @ORM\Column(type="datetime")
     */
    public ?\DateTime $ticketEndDate = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public ?string $textIntro = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public ?string $textHelp = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public ?string $textConfirmation = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $intoImagePath = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }
}

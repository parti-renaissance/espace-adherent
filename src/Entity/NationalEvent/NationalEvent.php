<?php

namespace App\Entity\NationalEvent;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityNameSlugTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NationalEvent\NationalEventRepository")
 */
class NationalEvent
{
    use EntityIdentityTrait;
    use EntityNameSlugTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(type="datetime")
     */
    #[Groups(['national_event_inscription:webhook'])]
    public ?\DateTime $startDate = null;

    /**
     * @ORM\Column(type="datetime")
     */
    #[Groups(['national_event_inscription:webhook'])]
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
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank(groups={"Admin"})
     */
    public ?string $textTicketEmail = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\NotBlank(groups={"Admin"})
     */
    public ?string $subjectTicketEmail = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank(groups={"Admin"})
     */
    public ?string $imageTicketEmail = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $intoImagePath = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $source = null;

    /**
     * @Assert\File(maxSize="5M", binaryFormat=false, mimeTypes={"image/*"})
     */
    public ?UploadedFile $file = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function isComplete(?string $source = null): bool
    {
        if ($source && $this->source === $source) {
            return false;
        }

        return $this->ticketEndDate < new \DateTime();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}

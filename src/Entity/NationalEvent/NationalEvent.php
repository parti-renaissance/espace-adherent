<?php

namespace App\Entity\NationalEvent;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityNameSlugTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\NotificationObjectInterface;
use App\Entity\UploadableFile;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\Repository\NationalEvent\NationalEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NationalEventRepository::class)]
class NationalEvent implements NotificationObjectInterface
{
    use EntityIdentityTrait;
    use EntityNameSlugTrait;
    use EntityTimestampableTrait;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTime $startDate = null;

    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTime $endDate = null;

    #[ORM\Column(type: 'datetime')]
    public ?\DateTime $ticketStartDate = null;

    #[ORM\Column(type: 'datetime')]
    public ?\DateTime $ticketEndDate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $textIntro = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $textHelp = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $textConfirmation = null;

    #[Assert\NotBlank(groups: ['Admin'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $textTicketEmail = null;

    #[Assert\NotBlank(groups: ['Admin'])]
    #[ORM\Column(nullable: true)]
    public ?string $subjectTicketEmail = null;

    #[Assert\NotBlank(groups: ['Admin'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $imageTicketEmail = null;

    #[ORM\Column(nullable: true)]
    public ?string $intoImagePath = null;

    #[ORM\Column(nullable: true)]
    public ?string $source = null;

    #[ORM\Column(nullable: true)]
    public ?string $ogTitle = null;

    #[ORM\Column(nullable: true)]
    public ?string $ogDescription = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $alertEnabled = false;

    #[ORM\Column(nullable: true)]
    public ?string $alertTitle = null;

    #[ORM\Column(nullable: true)]
    public ?string $alertDescription = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(cascade: ['all'], orphanRemoval: true)]
    public ?UploadableFile $ogImage = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(cascade: ['all'], orphanRemoval: true)]
    public ?UploadableFile $logoImage = null;

    #[Assert\File(maxSize: '5M', binaryFormat: false, mimeTypes: ['image/*'])]
    public ?UploadedFile $intoImageFile = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $inscriptionEditDeadline = null;

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

    public function allowEditInscription(): bool
    {
        return $this->inscriptionEditDeadline && $this->inscriptionEditDeadline > new \DateTime();
    }

    public function isNotificationEnabled(SendNotificationCommandInterface $command): bool
    {
        return true;
    }

    public function handleNotificationSent(SendNotificationCommandInterface $command): void
    {
    }
}

<?php

namespace App\Entity;

use App\EntityListener\AlgoliaIndexListener;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\EntityListeners([AlgoliaIndexListener::class])]
class TimelineItemPrivateMessage implements IndexableEntityInterface, NotificationObjectInterface, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $isActive = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $isNotificationActive = true;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $notificationSentAt = null;

    #[Assert\Count(min: 1)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: Adherent::class)]
    public Collection $adherents;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column]
    public string $title;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    public string $description;

    #[Assert\Expression('!this.isNotificationActive or this.notificationTitle')]
    #[ORM\Column(nullable: true)]
    public ?string $notificationTitle = null;

    #[Assert\Expression('!this.isNotificationActive or this.notificationDescription')]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $notificationDescription = null;

    #[ORM\Column(nullable: true)]
    public ?string $ctaLabel = null;

    #[ORM\Column(nullable: true)]
    public ?string $ctaUrl = null;

    #[ORM\Column(nullable: true)]
    public ?string $source = null;

    public function __construct(array $adherents = [])
    {
        $this->uuid = Uuid::uuid4();
        $this->adherents = new ArrayCollection($adherents);
    }

    public function isIndexable(): bool
    {
        return $this->isActive;
    }

    public function isNotificationEnabled(SendNotificationCommandInterface $command): bool
    {
        return $this->isNotificationActive;
    }

    public function handleNotificationSent(SendNotificationCommandInterface $command): void
    {
        $this->notificationSentAt = new \DateTimeImmutable();
    }

    public function countAdherents(): int
    {
        return $this->adherents->count();
    }

    public function __toString(): string
    {
        return $this->title;
    }
}

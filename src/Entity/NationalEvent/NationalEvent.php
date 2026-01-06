<?php

declare(strict_types=1);

namespace App\Entity\NationalEvent;

use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityNameSlugTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\NotificationObjectInterface;
use App\Entity\UploadableFile;
use App\JeMengage\Alert\AlertOwnerInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\NationalEvent\NationalEventTypeEnum;
use App\Repository\NationalEvent\NationalEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NationalEventRepository::class)]
class NationalEvent implements \Stringable, NotificationObjectInterface, EntityAdministratorBlameableInterface, AlertOwnerInterface
{
    use EntityIdentityTrait;
    use EntityNameSlugTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\NotBlank]
    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTime $startDate = null;

    #[Assert\NotBlank]
    #[Groups(['national_event_inscription:webhook'])]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTime $endDate = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTime $ticketStartDate = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTime $ticketEndDate = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $textIntro = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $textIntroContact = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $textHelp = null;

    #[Assert\NotBlank]
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
    public ?string $source = null;

    #[ORM\Column(nullable: true)]
    public ?string $ogTitle = null;

    #[ORM\Column(nullable: true)]
    public ?string $ogDescription = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $connectionEnabled = true;

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
    public ?UploadableFile $alertLogoImage = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(cascade: ['all'], orphanRemoval: true)]
    public ?UploadableFile $logoImage = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(cascade: ['all'], orphanRemoval: true)]
    public ?UploadableFile $intoImage = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $inscriptionEditDeadline = null;

    #[ORM\Column(enumType: NationalEventTypeEnum::class, options: ['default' => NationalEventTypeEnum::DEFAULT])]
    public NationalEventTypeEnum $type = NationalEventTypeEnum::DEFAULT;

    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $packageConfig = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $mailchimpSync = false;

    #[ORM\Column(nullable: true)]
    public ?string $defaultAccess = null;

    #[ORM\Column(nullable: true)]
    public ?string $defaultBracelet = null;

    #[ORM\Column(nullable: true)]
    public ?string $defaultBraceletColor = null;

    #[ORM\Column(nullable: true)]
    public ?string $programUrl = null;

    #[ORM\Column(nullable: true)]
    public ?string $discountLabel = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $discountHelp = null;

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

    public function isPaymentEnabled(): bool
    {
        return $this->isPackageEventType();
    }

    public function calculateInscriptionAmount(
        ?string $transport,
        ?string $accommodation,
        ?string $packagePlan,
        ?string $packageDonation,
        ?bool $withDiscount,
    ): ?int {
        $amountRows = [];

        if ($transport) {
            foreach ($this->getTransports()['options'] ?? [] as $transportConfig) {
                if ($transportConfig['id'] === $transport && !empty($transportConfig['montant'])) {
                    $amountRows['transport'] = (int) $transportConfig['montant'];
                    break;
                }
            }
        }

        if ($accommodation) {
            foreach ($this->getAccommodations()['options'] ?? [] as $accommodationConfig) {
                if ($accommodationConfig['id'] === $accommodation && !empty($accommodationConfig['montant'])) {
                    $amountRows['accommodation'] = (int) $accommodationConfig['montant'];
                    break;
                }
            }
        }

        if ($packagePlan) {
            foreach ($this->getPackagePlans()['options'] ?? [] as $packagePlanConfig) {
                if ($packagePlanConfig['id'] === $packagePlan && !empty($packagePlanConfig['montant'])) {
                    $amountRows['packagePlan'] = (int) $packagePlanConfig['montant'];
                    break;
                }
            }
        }

        if ($packageDonation) {
            foreach ($this->getPackageDonations()['options'] ?? [] as $packageDonationConfig) {
                if ($packageDonationConfig['id'] === $packageDonation && !empty($packageDonationConfig['montant'])) {
                    $amountRows['packageDonation'] = (int) $packageDonationConfig['montant'];
                    break;
                }
            }
        }

        if ($amountRows) {
            return (int) (array_sum($amountRows) * 100 * ($withDiscount ? $this->getDiscountFactor() : 1));
        }

        return null;
    }

    public function isPackageEventType(): bool
    {
        return \in_array($this->type, [NationalEventTypeEnum::CAMPUS, NationalEventTypeEnum::JEM], true);
    }

    public function getSortableAlertDate(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getIndexedPackageConfig(): ?array
    {
        if (null === $this->packageConfig) {
            return null;
        }

        return array_column($this->packageConfig, null, 'cle');
    }

    public function getVisitDays(): array
    {
        return $this->getIndexedPackageConfig()['visitDay'] ?? [];
    }

    public function getTransports(): array
    {
        return $this->getIndexedPackageConfig()['transport'] ?? [];
    }

    public function getPackagePlans(): array
    {
        return $this->getIndexedPackageConfig()['packagePlan'] ?? [];
    }

    public function getPackageCities(): array
    {
        return $this->getIndexedPackageConfig()['packageCity'] ?? [];
    }

    public function getPackageDepartureTimes(): array
    {
        return $this->getIndexedPackageConfig()['packageDepartureTime'] ?? [];
    }

    public function getPackageDonations(): array
    {
        return $this->getIndexedPackageConfig()['packageDonation'] ?? [];
    }

    public function getAccommodations(): array
    {
        return $this->getIndexedPackageConfig()['accommodation'] ?? [];
    }

    public function isNational(): bool
    {
        return true;
    }

    public function getDiscountFactor(): float
    {
        if (NationalEventTypeEnum::CAMPUS === $this->type) {
            return 0.5;
        }

        if (NationalEventTypeEnum::JEM === $this->type) {
            return 0;
        }

        return 1;
    }

    public function isJEM(): bool
    {
        return NationalEventTypeEnum::JEM === $this->type;
    }

    public function isCampus(): bool
    {
        return NationalEventTypeEnum::CAMPUS === $this->type;
    }
}

<?php

namespace App\Entity\GeneralConvention;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\GeneralConvention\MeetingTypeEnum;
use App\GeneralConvention\OrganizerEnum;
use App\GeneralConvention\ParticipantQuality;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/general_conventions',
            normalizationContext: ['groups' => ['general_convention_list']],
        ),
        new Get(
            uriTemplate: '/general_conventions/{uuid}',
            normalizationContext: ['groups' => ['general_convention_read']],
        ),
    ],
)]
#[ORM\Entity]
class GeneralConvention
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\NotBlank]
    #[Groups(['general_convention_list', 'general_convention_read'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    public ?Zone $departmentZone = null;

    #[Groups(['general_convention_list', 'general_convention_read'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Committee::class)]
    public ?Committee $committee = null;

    #[Groups(['general_convention_list', 'general_convention_read'])]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    public ?Zone $districtZone = null;

    #[Assert\NotBlank]
    #[Assert\Type(type: OrganizerEnum::class)]
    #[Groups(['general_convention_list', 'general_convention_read'])]
    #[ORM\Column(enumType: OrganizerEnum::class)]
    public ?OrganizerEnum $organizer = null;

    #[Assert\NotBlank]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $reporter = null;

    #[Assert\NotBlank]
    #[Groups(['general_convention_list', 'general_convention_read'])]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTimeInterface $reportedAt = null;

    #[Assert\NotBlank]
    #[Assert\Type(type: MeetingTypeEnum::class)]
    #[Groups(['general_convention_list', 'general_convention_read'])]
    #[ORM\Column(enumType: MeetingTypeEnum::class)]
    public ?MeetingTypeEnum $meetingType = null;

    #[Groups(['general_convention_list', 'general_convention_read'])]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    public int $membersCount = 0;

    #[Assert\NotBlank]
    #[Assert\Type(type: ParticipantQuality::class)]
    #[Groups(['general_convention_list', 'general_convention_read'])]
    #[ORM\Column(enumType: ParticipantQuality::class)]
    public ?ParticipantQuality $participantQuality = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $generalSummary = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $partyDefinitionSummary = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $uniquePartySummary = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $progressSince2016 = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $partyObjectives = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $governance = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $communication = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $militantTraining = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $memberJourney = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $mobilization = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $talentDetection = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $electionPreparation = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $relationshipWithSupporters = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $workWithPartners = null;

    #[Groups(['general_convention_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $additionalComments = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString(): string
    {
        return $this->departmentZone;
    }
}

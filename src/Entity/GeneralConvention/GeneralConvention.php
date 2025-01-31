<?php

namespace App\Entity\GeneralConvention;

use App\Entity\Adherent;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\GeneralConvention\MeetingTypeEnum;
use App\GeneralConvention\OrganizerEnum;
use App\GeneralConvention\ParticipantQuality;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'general_convention')]
class GeneralConvention
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    public ?Zone $departmentZone = null;

    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    public ?Zone $committeeZone = null;

    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    public ?Zone $districtZone = null;

    #[Assert\NotBlank]
    #[Assert\Type(type: OrganizerEnum::class)]
    #[ORM\Column(enumType: OrganizerEnum::class)]
    public ?OrganizerEnum $organizer = null;

    #[Assert\NotBlank]
    #[ORM\JoinColumn]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $reporter = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTimeInterface $reportedAt = null;

    #[Assert\NotBlank]
    #[Assert\Type(type: MeetingTypeEnum::class)]
    #[ORM\Column(enumType: MeetingTypeEnum::class)]
    public ?MeetingTypeEnum $meetingType = null;

    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    public int $membersCount = 0;

    #[Assert\NotBlank]
    #[Assert\Type(type: ParticipantQuality::class)]
    #[ORM\Column(enumType: ParticipantQuality::class)]
    public ?ParticipantQuality $participantQuality = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $generalSummary = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $partyDefinitionSummary = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $uniquePartySummary = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $progressSince2016 = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $partyObjectives = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $governance = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $communication = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $militantTraining = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $memberJourney = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $mobilization = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $talentDetection = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $electionPreparation = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $relationshipWithSupporters = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $workWithPartners = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $additionalComments = null;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function __toString(): string
    {
        return $this->departmentZone;
    }
}

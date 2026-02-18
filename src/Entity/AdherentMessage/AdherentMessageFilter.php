<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Collection\ZoneCollection;
use App\Entity\AdherentMessage\Segment\AudienceSegment;
use App\Entity\Committee;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\Geo\Zone;
use App\Entity\ZoneableEntityInterface;
use App\Validator\ManagedZone;
use App\Validator\ValidMessageFilterSegment;
use App\Validator\ValidScope;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    uriTemplate: '/v3/adherent_messages/{uuid}/filter',
    operations: [new Get(security: "is_granted('CAN_EDIT_PUBLICATION', object.getMessage())")],
    uriVariables: [
        'uuid' => new Link(fromProperty: 'filter', fromClass: AdherentMessage::class),
    ],
    normalizationContext: ['groups' => ['adherent_message_read_filter']],
)]
#[ManagedZone]
#[ORM\Entity]
#[ORM\Table(name: 'adherent_message_filters')]
#[ValidMessageFilterSegment]
class AdherentMessageFilter implements ZoneableEntityInterface, SegmentFilterInterface, AdherentMessageFilterInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityZoneTrait;

    /**
     * @var string|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    private $gender;

    /**
     * @var int|null
     */
    #[Assert\GreaterThanOrEqual(1)]
    #[Assert\LessThanOrEqual(200)]
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    private $ageMin;

    /**
     * @var int|null
     */
    #[Assert\GreaterThanOrEqual(1)]
    #[Assert\LessThanOrEqual(200)]
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    private $ageMax;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    private $firstName;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    private $lastName;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[ORM\Column(nullable: true)]
    private $city;

    /**
     * @var array|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private $interests = [];

    /**
     * @var \DateTime|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private $registeredSince;

    /**
     * @var \DateTime|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private $registeredUntil;

    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTime $firstMembershipSince = null;

    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTime $firstMembershipBefore = null;

    /**
     * @var \DateTime|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private $lastMembershipSince;

    /**
     * @var \DateTime|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private $lastMembershipBefore;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $synchronized = false;

    #[Groups(['adherent_message_read_filter'])]
    #[ORM\JoinColumn(name: 'adherent_message_filter_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'adherent_message_filter_zone')]
    #[ORM\ManyToMany(targetEntity: Zone::class, cascade: ['persist'])]
    protected Collection $zones;

    /**
     * @var AdherentMessageInterface
     */
    #[ORM\OneToOne(mappedBy: 'filter', targetEntity: AdherentMessage::class)]
    private $message;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne]
    protected ?Committee $committee = null;

    /**
     * @var AudienceSegment|null
     */
    #[Groups(['adherent_message_update_filter'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: AudienceSegment::class)]
    private $segment;

    /**
     * @var bool|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $isCertified;

    /**
     * @var Zone|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    private $zone;

    /**
     * @var string|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(nullable: true)]
    #[ValidScope]
    private $scope;

    /**
     * @var string|null
     */
    #[Groups(['adherent_message_update_filter'])]
    #[ORM\Column(nullable: true)]
    private $audienceType;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isCommitteeMember = null;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    private ?string $mandateType = null;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    private ?string $declaredMandate = null;

    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected ?bool $isCampusRegistered = null;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    private ?string $donatorStatus = null;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    public ?string $adherentTags = null;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    public ?string $electTags = null;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    public ?string $staticTags = null;

    #[ORM\Column(nullable: true)]
    private ?string $mandate = null;

    #[ORM\Column(nullable: true)]
    private ?string $politicalFunction = null;

    #[ORM\Column(nullable: true)]
    public ?string $label = null;

    #[ORM\Column(nullable: true)]
    public ?string $postalCode = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    public ?bool $includeAdherentsNoCommittee = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    public ?bool $includeAdherentsInCommittee = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    public ?bool $includeCommitteeSupervisors = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    public ?bool $includeCommitteeHosts = null;

    // Keep old dtype information
    #[ORM\Column(nullable: true)]
    private ?string $dtype = null;

    public function __construct(array $zones = [])
    {
        $this->uuid = Uuid::uuid4();
        $this->zones = new ZoneCollection($zones);
    }

    #[Groups(['audience_segment_read'])]
    public function getIsCommitteeMember(): ?bool
    {
        return $this->isCommitteeMember;
    }

    #[Groups(['audience_segment_write'])]
    public function setIsCommitteeMember(?bool $value): void
    {
        $this->isCommitteeMember = $value;
    }

    public function getIsCertified(): ?bool
    {
        return $this->isCertified;
    }

    public function setIsCertified(?bool $isCertified): void
    {
        $this->isCertified = $isCertified;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }

    #[Groups(['audience_segment_write', 'adherent_message_update_filter'])]
    public function setAge(?array $minMax): void
    {
        $this->setAgeMin(empty($minMax['min']) ? null : $minMax['min']);
        $this->setAgeMax(empty($minMax['max']) ? null : $minMax['max']);
    }

    #[Groups(['audience_segment_write', 'adherent_message_update_filter'])]
    public function setRegistered(?array $startEnd): void
    {
        $this->setRegisteredSince(empty($startEnd['start']) ? null : new \DateTime($startEnd['start']));
        $this->setRegisteredUntil(empty($startEnd['end']) ? null : new \DateTime($startEnd['end']));
    }

    #[Groups(['audience_segment_write', 'adherent_message_update_filter'])]
    public function setFirstMembership(?array $startEnd): void
    {
        $this->firstMembershipSince = empty($startEnd['start']) ? null : new \DateTime($startEnd['start']);
        $this->firstMembershipBefore = empty($startEnd['end']) ? null : new \DateTime($startEnd['end']);
    }

    #[Groups(['audience_segment_write', 'adherent_message_update_filter'])]
    public function setLastMembership(?array $startEnd): void
    {
        $this->setLastMembershipSince(empty($startEnd['start']) ? null : new \DateTime($startEnd['start']));
        $this->setLastMembershipBefore(empty($startEnd['end']) ? null : new \DateTime($startEnd['end']));
    }

    public function getAudienceType(): ?string
    {
        return $this->audienceType;
    }

    public function setAudienceType(?string $audienceType): void
    {
        $this->audienceType = $audienceType;
    }

    public function getMandateType(): ?string
    {
        return $this->mandateType;
    }

    public function setMandateType(?string $mandateType): void
    {
        $this->mandateType = $mandateType;
    }

    public function getDeclaredMandate(): ?string
    {
        return $this->declaredMandate;
    }

    public function setDeclaredMandate(?string $declaredMandate): void
    {
        $this->declaredMandate = $declaredMandate;
    }

    public function getIsCampusRegistered(): ?bool
    {
        return $this->isCampusRegistered;
    }

    public function setIsCampusRegistered(?bool $isCampusRegistered): void
    {
        $this->isCampusRegistered = $isCampusRegistered;
    }

    public function getDonatorStatus(): ?string
    {
        return $this->donatorStatus;
    }

    public function setDonatorStatus(?string $donatorStatus): void
    {
        $this->donatorStatus = $donatorStatus;
    }

    public function includeFilter(string $value): bool
    {
        return !str_starts_with($value, '!');
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getAgeMin(): ?int
    {
        return $this->ageMin;
    }

    public function setAgeMin(?int $ageMin): void
    {
        $this->ageMin = $ageMin;
    }

    public function getAgeMax(): ?int
    {
        return $this->ageMax;
    }

    public function setAgeMax(?int $ageMax): void
    {
        $this->ageMax = $ageMax;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getInterests(): ?array
    {
        return $this->interests;
    }

    public function setInterests(?array $interests): void
    {
        $this->interests = $interests;
    }

    public function getCityAsArray(): array
    {
        return $this->city ? array_map('trim', explode(',', $this->city)) : [];
    }

    public function getRegisteredSince(): ?\DateTime
    {
        return $this->registeredSince;
    }

    public function setRegisteredSince(?\DateTime $registeredSince): void
    {
        $this->registeredSince = $registeredSince;
    }

    public function getRegisteredUntil(): ?\DateTime
    {
        return $this->registeredUntil;
    }

    public function setRegisteredUntil(?\DateTime $registeredUntil): void
    {
        $this->registeredUntil = $registeredUntil;
    }

    public function getLastMembershipSince(): ?\DateTime
    {
        return $this->lastMembershipSince;
    }

    public function setLastMembershipSince(?\DateTime $lastMembershipSince): void
    {
        $this->lastMembershipSince = $lastMembershipSince;
    }

    public function getLastMembershipBefore(): ?\DateTime
    {
        return $this->lastMembershipBefore;
    }

    public function setLastMembershipBefore(?\DateTime $lastMembershipBefore): void
    {
        $this->lastMembershipBefore = $lastMembershipBefore;
    }

    public function reset(): void
    {
        $this->gender = null;
        $this->ageMin = null;
        $this->ageMax = null;
        $this->firstName = null;
        $this->lastName = null;
        $this->city = null;
        $this->interests = [];
        $this->registeredSince = null;
        $this->registeredUntil = null;
        $this->firstMembershipSince = null;
        $this->firstMembershipBefore = null;
        $this->lastMembershipSince = null;
        $this->lastMembershipBefore = null;
        $this->segment = null;
        $this->isCertified = null;
        $this->audienceType = null;
        $this->committee = null;
        $this->isCommitteeMember = null;
        $this->mandateType = null;
        $this->declaredMandate = null;
        $this->isCampusRegistered = null;
        $this->donatorStatus = null;
        $this->adherentTags = null;
        $this->electTags = null;
        $this->staticTags = null;
    }

    public function setSynchronized(bool $value): void
    {
        $this->synchronized = $value;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function getExternalId(): ?string
    {
        return $this->message->getExternalId();
    }

    public function getMessage(): ?AdherentMessageInterface
    {
        return $this->message;
    }

    public function setMessage(AdherentMessageInterface $message): void
    {
        $this->message = $message;
    }

    public function getSegment(): ?AudienceSegment
    {
        return $this->segment;
    }

    public function setSegment(?AudienceSegment $segment): void
    {
        $this->segment = $segment;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(?Committee $committee): void
    {
        $this->committee = $committee;
    }
}

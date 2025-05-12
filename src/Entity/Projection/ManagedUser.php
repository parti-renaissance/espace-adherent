<?php

namespace App\Entity\Projection;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Adherent\Tag\TranslatedTagInterface;
use App\Collection\ZoneCollection;
use App\Controller\Api\AdherentList\AdherentListController;
use App\Entity\EntityZoneTrait;
use App\Entity\Geo\Zone;
use App\Entity\ImageAwareInterface;
use App\Entity\ImageExposeInterface;
use App\Entity\ImageTrait;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Normalizer\ImageExposeNormalizer;
use App\Repository\Projection\ManagedUserRepository;
use App\Subscription\SubscriptionTypeEnum;
use App\ValueObject\Genders;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * This entity is a projection: do not insert, update or delete objects using this class.
 * The table is populated on a regular basis by a background worker to improve performance
 * of SQL queries.
 */
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/adherents/{adherentUuid}',
            requirements: ['adherentUuid' => '%pattern_uuid%'],
            normalizationContext: [
                'enable_tag_translator' => true,
                'groups' => ['managed_user_read', ImageExposeNormalizer::NORMALIZATION_GROUP],
            ],
        ),
        new GetCollection(
            uriTemplate: '/v3/adherents.{format}',
            defaults: ['format' => 'json'],
            requirements: ['format' => 'json|csv|xlsx'],
            controller: AdherentListController::class,
            read: false,
        ),
    ]
)]
#[ORM\Entity(repositoryClass: ManagedUserRepository::class, readOnly: true)]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['original_id'])]
#[ORM\Index(columns: ['zones_ids'])]
#[ORM\Table(name: 'projection_managed_users')]
class ManagedUser implements TranslatedTagInterface, ImageAwareInterface, ImageExposeInterface
{
    use EntityZoneTrait;
    use ImageTrait;

    public const STATUS_READY = 1;

    /**
     * @var int
     */
    #[ApiProperty(identifier: false)]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var int
     */
    #[ORM\Column(type: 'smallint')]
    private $status;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $adherentStatus;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $activatedAt;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $source;

    /**
     * @var int
     */
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    private $originalId;

    /**
     * @var UuidInterface|null
     */
    #[ApiProperty(identifier: true)]
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'uuid', nullable: true)]
    private $adherentUuid;

    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(length: 7, nullable: true)]
    public ?string $publicId = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $lastLoggedAt = null;

    /**
     * @var string
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column]
    private $email;

    #[ORM\Column(nullable: true)]
    public ?string $mailchimpStatus = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $resubscribeEmailSentAt = null;

    /**
     * @var string|null
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(length: 150, nullable: true)]
    private $address;

    /**
     * @var string|null
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(length: 15, nullable: true)]
    private $postalCode;

    /**
     * The postal code is filled only for committee supervisors.
     *
     * @var string|null
     */
    #[ORM\Column(length: 15, nullable: true)]
    private $committeePostalCode;

    /**
     * @var string|null
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(nullable: true)]
    private $city;

    /**
     * @var string|null
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(length: 2, nullable: true)]
    private $country;

    /**
     * @var string|null
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(length: 6, nullable: true)]
    private $gender;

    /**
     * @var string|null
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private $firstName;

    /**
     * @var string|null
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(length: 50, nullable: true)]
    private $lastName;

    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private $birthdate;

    /**
     * @var int|null
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'smallint', nullable: true)]
    private $age;

    /**
     * @var PhoneNumber|null
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    private $phone;

    /**
     * @var string|null
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(length: 2, nullable: true)]
    private $nationality;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $committees;

    /**
     * @var string[]|null
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $committeeUuids;

    /**
     * @var string[]|null
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $roles;

    /**
     * @var string[]|null
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    public ?array $tags = null;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private $isCommitteeMember;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private $isCommitteeHost;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private $isCommitteeSupervisor;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private $isCommitteeProvisionalSupervisor;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $subscribedTags;

    /**
     * @var \DateTime|null
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $interests;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $supervisorTags;

    /**
     * @var array
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $subscriptionTypes;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $voteCommitteeId;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $certifiedAt;

    /**
     * @var \DateTime|null
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $lastMembershipDonation = null;

    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $firstMembershipDonation = null;

    /**
     * name of committee v2
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(nullable: true)]
    private ?string $committee;

    /**
     * uuid of committee v2
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?UuidInterface $committeeUuid;

    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(nullable: true)]
    private ?string $agora;

    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?UuidInterface $agoraUuid;

    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private ?array $mandates;

    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private ?array $declaredMandates;

    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    public ?array $cotisationDates = null;

    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $campusRegisteredAt;

    #[ORM\Column(nullable: true)]
    private ?string $zonesIds;

    /**
     * @var ZoneCollection|Zone[]
     */
    #[Groups(['phoning_campaign_read', 'phoning_campaign_write', 'read_api', 'managed_users_list', 'managed_user_read'])]
    #[ORM\JoinTable(name: 'projection_managed_users_zone')]
    #[ORM\ManyToMany(targetEntity: Zone::class, cascade: ['persist'])]
    protected Collection $zones;

    public function __construct(
        int $status,
        ?string $source,
        int $originalId,
        string $email,
        string $address,
        string $postalCode,
        ?string $committeePostalCode = null,
        ?string $city = null,
        ?string $country = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?\DateTime $birthdate = null,
        ?int $age = null,
        ?PhoneNumber $phone = null,
        ?string $nationality = null,
        ?string $committees = null,
        ?array $committeeUuids = null,
        ?array $tags = null,
        int $isCommitteeMember = 0,
        int $isCommitteeHost = 0,
        int $isCommitteeProvisionalSupervisor = 0,
        int $isCommitteeSupervisor = 0,
        ?array $subscriptionTypes = [],
        array $zones = [],
        ?string $subscribedTags = null,
        ?\DateTime $createdAt = null,
        ?string $gender = null,
        array $supervisorTags = [],
        ?UuidInterface $uuid = null,
        ?int $voteCommitteeId = null,
        ?\DateTime $certifiedAt = null,
        ?\DateTime $lastMembershipDonation = null,
        ?\DateTime $firstMembershipDonation = null,
        ?string $committee = null,
        ?UuidInterface $committeeUuid = null,
        ?string $agora = null,
        ?UuidInterface $agoraUuid = null,
        array $interests = [],
        array $mandates = [],
        array $declaredMandates = [],
        array $cotisationDates = [],
        ?\DateTime $campusRegisteredAt = null,
    ) {
        $this->status = $status;
        $this->source = $source;
        $this->originalId = $originalId;
        $this->adherentUuid = $uuid;
        $this->email = $email;
        $this->address = $address;
        $this->postalCode = $postalCode;
        $this->committeePostalCode = $committeePostalCode;
        $this->city = $city;
        $this->country = $country;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->birthdate = $birthdate;
        $this->age = $age;
        $this->phone = $phone;
        $this->nationality = $nationality;
        $this->committees = $committees;
        $this->committeeUuids = $committeeUuids;
        $this->tags = $tags;
        $this->isCommitteeMember = $isCommitteeMember;
        $this->isCommitteeHost = $isCommitteeHost;
        $this->isCommitteeSupervisor = $isCommitteeSupervisor;
        $this->isCommitteeProvisionalSupervisor = $isCommitteeProvisionalSupervisor;
        $this->subscriptionTypes = $subscriptionTypes;
        $this->subscribedTags = $subscribedTags;
        $this->createdAt = $createdAt;
        $this->certifiedAt = $certifiedAt;
        $this->lastMembershipDonation = $lastMembershipDonation;
        $this->firstMembershipDonation = $firstMembershipDonation;
        $this->gender = $gender;
        $this->supervisorTags = $supervisorTags;
        $this->voteCommitteeId = $voteCommitteeId;
        $this->zones = new ZoneCollection($zones);
        $this->zonesIds = implode(',', array_unique(array_map(
            fn (Zone $zone) => $zone->getId(),
            array_merge(...array_map(fn (Zone $zone) => $zone->getWithParents(), $zones))
        )));
        $this->interests = $interests;
        $this->committee = $committee;
        $this->committeeUuid = $committeeUuid;
        $this->agora = $agora;
        $this->agoraUuid = $agoraUuid;
        $this->mandates = $mandates;
        $this->declaredMandates = $declaredMandates;
        $this->cotisationDates = $cotisationDates;
        $this->campusRegisteredAt = $campusRegisteredAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getOriginalId(): int
    {
        return $this->originalId;
    }

    public function getAdherentUuid(): ?UuidInterface
    {
        return $this->adherentUuid;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getCommitteePostalCode(): ?string
    {
        return $this->committeePostalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function getFullName(): ?string
    {
        return $this->firstName ? $this->firstName.' '.$this->lastName : null;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function getCommittees(): array
    {
        return $this->committees ? explode('|', $this->committees) : [];
    }

    public function isCommitteeMember(): bool
    {
        return $this->isCommitteeMember;
    }

    public function isCommitteeHost(): bool
    {
        return $this->isCommitteeHost;
    }

    public function isCommitteeSupervisor(): bool
    {
        return $this->isCommitteeSupervisor;
    }

    public function isCommitteeProvisionalSupervisor(): bool
    {
        return $this->isCommitteeProvisionalSupervisor;
    }

    public function getSubscribedTags(): string
    {
        return $this->subscribedTags;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }

    public function getSupervisorTags(): array
    {
        return $this->supervisorTags;
    }

    public function getSubscriptionTypes(): array
    {
        return $this->subscriptionTypes;
    }

    public function hasSmsSubscriptionType(): bool
    {
        return $this->phone && \in_array(SubscriptionTypeEnum::MILITANT_ACTION_SMS, $this->subscriptionTypes, true);
    }

    public function isEmailSubscribed(): bool
    {
        return ContactStatusEnum::SUBSCRIBED === $this->mailchimpStatus;
    }

    #[Groups(['managed_users_list', 'managed_user_read'])]
    public function isAvailableForResubscribeEmail(): bool
    {
        return !$this->isEmailSubscribed() && (!$this->resubscribeEmailSentAt || $this->resubscribeEmailSentAt->diff(new \DateTime())->y >= 1);
    }

    public function getCommitteeUuids(): ?array
    {
        return $this->committeeUuids;
    }

    #[Groups(['managed_users_list', 'managed_user_read'])]
    #[SerializedName('roles')]
    public function getRolesAsArray(): array
    {
        $roles = [];

        foreach ($this->roles as $role) {
            $roleData = [
                'role' => $role,
            ];

            if (str_contains($role, '|')) {
                $rolePart = explode('|', $role);

                $roleData['role'] = $rolePart[0];
                $roleData['is_delegated'] = true;
                $roleData['function'] = $rolePart[1];
            }
            $roles[] = $roleData;
        }

        return $roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getCommitteesAsString(string $separator = ' / '): string
    {
        return implode($separator, $this->getCommittees());
    }

    public function getCivilityLabel(): string
    {
        return match ($this->gender) {
            Genders::MALE => 'M',
            Genders::FEMALE => 'Mme',
            default => '',
        };
    }

    public function getVoteCommitteeId(): ?int
    {
        return $this->voteCommitteeId;
    }

    #[Groups(['managed_users_list', 'managed_user_read'])]
    public function isCertified(): bool
    {
        return null !== $this->certifiedAt;
    }

    #[Groups(['managed_users_list'])]
    public function getCityCode(): ?string
    {
        $zones = $this->getZonesOfType(Zone::CITY, true);

        return $zones ? current($zones)->getCode() : null;
    }

    #[Groups(['managed_users_list', 'managed_user_read'])]
    public function getSmsSubscription(): bool
    {
        return \in_array(SubscriptionTypeEnum::MILITANT_ACTION_SMS, $this->subscriptionTypes, true);
    }

    public function getCommittee(): ?string
    {
        return $this->committee;
    }

    public function getCommitteeUuid(): ?UuidInterface
    {
        return $this->committeeUuid;
    }

    public function getAgora(): ?string
    {
        return $this->agora;
    }

    public function getAgoraUuid(): ?UuidInterface
    {
        return $this->agoraUuid;
    }

    public function getMandates(): ?array
    {
        return $this->mandates;
    }

    public function getDeclaredMandates(): ?array
    {
        return $this->declaredMandates;
    }

    public function getCampusRegisteredAt(): ?\DateTime
    {
        return $this->campusRegisteredAt;
    }

    public function getImagePath(): string
    {
        return $this->imageName ? \sprintf('images/profile/%s', $this->getImageName()) : '';
    }
}

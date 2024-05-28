<?php

namespace App\Entity\Projection;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Adherent\Tag\TranslatedTagInterface;
use App\Collection\ZoneCollection;
use App\Entity\EntityZoneTrait;
use App\Entity\Geo\Zone;
use App\Membership\MembershipSourceEnum;
use App\Renaissance\Membership\RenaissanceMembershipFilterEnum;
use App\Subscription\SubscriptionTypeEnum;
use App\ValueObject\Genders;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * This entity is a projection: do not insert, update or delete objects using this class.
 * The table is populated on a regular basis by a background worker to improve performance
 * of SQL queries.
 *
 * @ORM\Table(name="projection_managed_users", indexes={
 *     @ORM\Index(columns={"status"}),
 *     @ORM\Index(columns={"original_id"}),
 *     @ORM\Index(columns={"zones_ids"}),
 * })
 * @ORM\Entity(readOnly=true, repositoryClass="App\Repository\Projection\ManagedUserRepository")
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="zones",
 *         joinTable=@ORM\JoinTable(
 *             name="projection_managed_users_zone"
 *         )
 *     )
 * })
 *
 * @ApiResource(
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/adherents/{adherentUuid}",
 *             "requirements": {"adherentUuid": "%pattern_uuid%"},
 *             "normalization_context": {
 *                 "enable_tag_translator": true,
 *                 "groups": {"managed_user_read"}
 *             },
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/adherents.{format}",
 *             "controller": "App\Controller\Api\AdherentList\AdherentListController",
 *             "requirements": {
 *                 "format": "json|csv|xlsx",
 *             },
 *             "defaults": {
 *                 "format": "json",
 *             },
 *         },
 *     },
 * )
 */
class ManagedUser implements TranslatedTagInterface
{
    use EntityZoneTrait;

    public const STATUS_READY = 1;

    /**
     * @var int
     *
     * @ApiProperty(identifier=false)
     *
     * @ORM\Id
     * @ORM\Column(type="bigint", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $adherentStatus;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activatedAt;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $source;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", options={"unsigned": true})
     */
    private $originalId;

    /**
     * @var UuidInterface|null
     *
     * @ApiProperty(identifier=true)
     *
     * @ORM\Column(type="uuid", nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $adherentUuid;

    /**
     * @var string
     *
     * @ORM\Column
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(length=150, nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $address;

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $postalCode;

    /**
     * The postal code is filled only for committee supervisors.
     *
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     */
    private $committeePostalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(length=2, nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $country;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6, nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $gender;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $lastName;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $birthdate;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $age;

    /**
     * @var PhoneNumber|null
     *
     * @ORM\Column(type="phone_number", nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(length=2, nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $nationality;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $committees;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $committeeUuids;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $roles;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    public ?array $tags = null;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isCommitteeMember;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isCommitteeHost;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isCommitteeSupervisor;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isCommitteeProvisionalSupervisor;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $subscribedTags;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime")
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $createdAt;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $interests;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $supervisorTags;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $subscriptionTypes;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $voteCommitteeId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $certifiedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private $lastMembershipDonation;

    /**
     * name of committee v2
     *
     * @ORM\Column(nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private ?string $committee;

    /**
     * uuid of committee v2
     *
     * @ORM\Column(type="uuid", nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private ?UuidInterface $committeeUuid;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    #[Groups(['managed_users_list'])]
    private ?array $additionalTags;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private ?array $mandates;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private ?array $declaredMandates;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    public ?array $cotisationDates = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[Groups(['managed_users_list', 'managed_user_read'])]
    private ?\DateTime $campusRegisteredAt;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $zonesIds;

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
        ?array $additionalTags = null,
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
        ?string $committee = null,
        ?UuidInterface $committeeUuid = null,
        array $interests = [],
        array $mandates = [],
        array $declaredMandates = [],
        array $cotisationDates = [],
        ?\DateTime $campusRegisteredAt = null
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
        $this->additionalTags = $additionalTags;
        $this->isCommitteeMember = $isCommitteeMember;
        $this->isCommitteeHost = $isCommitteeHost;
        $this->isCommitteeSupervisor = $isCommitteeSupervisor;
        $this->isCommitteeProvisionalSupervisor = $isCommitteeProvisionalSupervisor;
        $this->subscriptionTypes = $subscriptionTypes;
        $this->subscribedTags = $subscribedTags;
        $this->createdAt = $createdAt;
        $this->certifiedAt = $certifiedAt;
        $this->lastMembershipDonation = $lastMembershipDonation;
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

    public function getGenderLabel(): string
    {
        switch ($this->gender) {
            case Genders::MALE:
                return 'Homme';

            case Genders::FEMALE:
                return 'Femme';

            default:
                return 'Autre';
        }
    }

    public function getUserRoleLabels($separator = ' / '): string
    {
        if ($this->isCommitteeSupervisor || $this->isCommitteeHost) {
            $roles = [];

            if ($this->isCommitteeSupervisor) {
                $roles[] = 'Animateur local';
            }

            if ($this->isCommitteeProvisionalSupervisor) {
                $roles[] = 'Animateur local provisoire';
            }

            if ($this->isCommitteeHost) {
                $roles[] = 'Co-animateur local';
            }

            return implode($separator, $roles);
        }

        return 'Adherent';
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

    public function getLastMembershipDonation(): ?\DateTime
    {
        return $this->lastMembershipDonation;
    }

    #[Groups(['managed_users_list'])]
    public function getRenaissanceMembership(): ?string
    {
        if (MembershipSourceEnum::RENAISSANCE === $this->source) {
            return null !== $this->lastMembershipDonation
                ? RenaissanceMembershipFilterEnum::ADHERENT_RE
                : RenaissanceMembershipFilterEnum::SYMPATHIZER_RE;
        }

        return null;
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

    public function getAdditionalTags(): ?array
    {
        return $this->additionalTags;
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
}

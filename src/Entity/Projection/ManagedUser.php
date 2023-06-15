<?php

namespace App\Entity\Projection;

use App\Entity\EntityZoneTrait;
use App\Entity\Geo\Zone;
use App\Membership\MembershipSourceEnum;
use App\Renaissance\Membership\RenaissanceMembershipFilterEnum;
use App\Subscription\SubscriptionTypeEnum;
use App\Utils\PhoneNumberUtils;
use App\ValueObject\Genders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * This entity is a projection: do not insert, update or delete objects using this class.
 * The table is populated on a regular basis by a background worker to improve performance
 * of SQL queries.
 *
 * @ORM\Table(name="projection_managed_users", indexes={
 *     @ORM\Index(columns={"status"}),
 *     @ORM\Index(columns={"original_id"}),
 * })
 * @ORM\Entity(readOnly=true, repositoryClass="App\Repository\Projection\ManagedUserRepository")
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="zones",
 *         joinTable=@ORM\JoinTable(
 *             name="projection_managed_users_zone"
 *         )
 *     )
 * })
 */
class ManagedUser
{
    use EntityZoneTrait;

    public const STATUS_READY = 1;

    private const STYLE_TYPE_ADHERENT = 'adherent';
    private const STYLE_TYPE_HOST = 'host';

    /**
     * @var int
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
     * @ORM\Column(type="uuid", nullable=true)
     */
    private $adherentUuid;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Groups({"managed_user_read"})
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(length=150, nullable=true)
     *
     * @Groups({"managed_user_read"})
     */
    private $address;

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Groups({"managed_user_read"})
     */
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
     *
     * @Groups({"managed_user_read"})
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(length=2, nullable=true)
     *
     * @Groups({"managed_user_read"})
     */
    private $country;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     *
     * @Groups({"managed_user_read"})
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     *
     * @Groups({"managed_user_read"})
     */
    private $lastName;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $age;

    /**
     * @var PhoneNumber|null
     *
     * @ORM\Column(type="phone_number", nullable=true)
     */
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(length=2, nullable=true)
     *
     * @Groups({"managed_user_read"})
     */
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
     *
     * @Groups({"managed_user_read"})
     */
    private $createdAt;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6, nullable=true)
     *
     * @Groups({"managed_user_read"})
     */
    private $gender;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Groups({"managed_user_read"})
     */
    private $interests = [];

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
     *
     * @Groups({"managed_user_read"})
     */
    private $lastMembershipDonation;

    /**
     * name of committee v2
     *
     * @ORM\Column(nullable=true)
     *
     * @Groups({"managed_user_read"})
     */
    private ?string $committee;

    /**
     * uuid of committee v2
     *
     * @ORM\Column(type="uuid", nullable=true)
     *
     * @Groups({"managed_user_read"})
     */
    private ?UuidInterface $committeeUuid;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Groups({"managed_user_read"})
     */
    private ?array $tags;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private ?array $mandateTypes;

    public function __construct(
        int $status,
        ?string $source,
        int $originalId,
        string $email,
        string $address,
        string $postalCode,
        string $committeePostalCode = null,
        string $city = null,
        string $country = null,
        string $firstName = null,
        string $lastName = null,
        int $age = null,
        PhoneNumber $phone = null,
        string $nationality = null,
        string $committees = null,
        array $committeeUuids = null,
        array $tags = null,
        int $isCommitteeMember = 0,
        int $isCommitteeHost = 0,
        int $isCommitteeProvisionalSupervisor = 0,
        int $isCommitteeSupervisor = 0,
        ?array $subscriptionTypes = [],
        array $zones = [],
        string $subscribedTags = null,
        \DateTime $createdAt = null,
        string $gender = null,
        array $supervisorTags = [],
        UuidInterface $uuid = null,
        int $voteCommitteeId = null,
        \DateTime $certifiedAt = null,
        \DateTime $lastMembershipDonation = null,
        string $committee = null,
        UuidInterface $committeeUuid = null,
        array $interests = [],
        array $mandateTypes = null
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
        $this->gender = $gender;
        $this->supervisorTags = $supervisorTags;
        $this->voteCommitteeId = $voteCommitteeId;
        $this->zones = new ArrayCollection($zones);
        $this->interests = $interests;
        $this->committee = $committee;
        $this->committeeUuid = $committeeUuid;
        $this->mandateTypes = $mandateTypes;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getStyleType(): string
    {
        if ($this->isCommitteeHost || $this->isCommitteeProvisionalSupervisor || $this->isCommitteeSupervisor) {
            return self::STYLE_TYPE_HOST;
        }

        return self::STYLE_TYPE_ADHERENT;
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
        return \in_array(SubscriptionTypeEnum::MILITANT_ACTION_SMS, $this->subscriptionTypes, true);
    }

    public function getCommitteeUuids(): ?array
    {
        return $this->committeeUuids;
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

    public function isCertified(): bool
    {
        return null !== $this->certifiedAt;
    }

    public function getLastMembershipDonation(): ?\DateTime
    {
        return $this->lastMembershipDonation;
    }

    /**
     * @Groups({"managed_user_read"})
     */
    public function getRenaissanceMembership(): ?string
    {
        if (MembershipSourceEnum::RENAISSANCE === $this->source) {
            return null !== $this->lastMembershipDonation
                ? RenaissanceMembershipFilterEnum::ADHERENT_RE
                : RenaissanceMembershipFilterEnum::SYMPATHIZER_RE
            ;
        }

        return null;
    }

    /**
     * @Groups({"managed_user_read"})
     */
    public function getCityCode(): ?string
    {
        $zones = $this->getZonesOfType(Zone::CITY, true);

        return $zones ? current($zones)->getCode() : null;
    }

    public function getDepartmentCode(): ?string
    {
        $zones = $this->getZonesOfType(Zone::DEPARTMENT, true);

        return $zones ? current($zones)->getCode() : null;
    }

    public function getDepartment(): ?string
    {
        $zones = $this->getZonesOfType(Zone::DEPARTMENT, true);

        return $zones ? current($zones)->getName() : null;
    }

    public function getRegionCode(): ?string
    {
        $zones = $this->getZonesOfType(Zone::REGION, true);

        return $zones ? current($zones)->getCode() : null;
    }

    public function getRegion(): ?string
    {
        $zones = $this->getZonesOfType(Zone::REGION, true);

        return $zones ? current($zones)->getName() : null;
    }

    /**
     * @Groups({"managed_user_read"})
     */
    public function getSmsSubscription(): bool
    {
        return \in_array(SubscriptionTypeEnum::MILITANT_ACTION_SMS, $this->subscriptionTypes, true);
    }

    /**
     * @Groups({"managed_user_read"})
     */
    public function getPhoneNumber(): ?string
    {
        return $this->getPhone() ? PhoneNumberUtils::format($this->getPhone()) : null;
    }

    public function getCommittee(): ?string
    {
        return $this->committee;
    }

    public function getCommitteeUuid(): ?UuidInterface
    {
        return $this->committeeUuid;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function getMandateTypes(): ?array
    {
        return $this->mandateTypes;
    }
}

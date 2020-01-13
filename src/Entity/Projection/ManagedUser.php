<?php

namespace AppBundle\Entity\Projection;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Subscription\SubscriptionTypeEnum;
use AppBundle\ValueObject\Genders;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\UuidInterface;

/**
 * This entity is a projection: do not insert, update or delete objects using this class.
 * The table is populated on a regular basis by a background worker to improve performance
 * of SQL queries.
 *
 * @ORM\Table(name="projection_managed_users", indexes={
 *     @ORM\Index(name="projection_managed_users_search", columns={"status", "postal_code", "country"})
 * })
 * @ORM\Entity(readOnly=true, repositoryClass="AppBundle\Repository\Projection\ManagedUserRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ManagedUser
{
    public const STATUS_READY = 1;
    public const TYPE_ADHERENT = 'adherent';

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
     * @var string
     *
     * @ORM\Column(length=20)
     */
    private $type;

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
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
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
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(length=2, nullable=true)
     */
    private $country;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
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
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $committees;

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
    private $createdAt;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6, nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $interests = [];

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $supervisorTags;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $citizenProjects;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $citizenProjectsOrganizer;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $subscriptionTypes;

    public function __construct(
        int $status,
        string $type,
        int $originalId,
        string $email,
        string $postalCode,
        string $committeePostalCode = null,
        string $city = null,
        string $country = null,
        string $firstName = null,
        string $lastName = null,
        int $age = null,
        PhoneNumber $phone = null,
        string $committees = null,
        int $isCommitteeMember = 0,
        int $isCommitteeHost = 0,
        int $isCommitteeSupervisor = 0,
        array $subscriptionTypes = [],
        string $subscribedTags = null,
        \DateTime $createdAt = null,
        string $gender = null,
        array $supervisorTags = [],
        array $citizenProjects = null,
        array $citizenProjectsOrganizer = null,
        UuidInterface $uuid = null
    ) {
        $this->status = $status;
        $this->type = $type;
        $this->originalId = $originalId;
        $this->adherentUuid = $uuid;
        $this->email = $email;
        $this->postalCode = $postalCode;
        $this->committeePostalCode = $committeePostalCode;
        $this->city = $city;
        $this->country = $country;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->age = $age;
        $this->phone = $phone;
        $this->committees = $committees;
        $this->isCommitteeMember = $isCommitteeMember;
        $this->isCommitteeHost = $isCommitteeHost;
        $this->isCommitteeSupervisor = $isCommitteeSupervisor;
        $this->subscriptionTypes = $subscriptionTypes;
        $this->subscribedTags = $subscribedTags;
        $this->createdAt = $createdAt;
        $this->gender = $gender;
        $this->supervisorTags = $supervisorTags;
        $this->citizenProjects = $citizenProjects;
        $this->citizenProjectsOrganizer = $citizenProjectsOrganizer;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getStyleType(): string
    {
        if ($this->isCommitteeHost || $this->isCommitteeSupervisor) {
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

    public function setAdherentUuid(?UuidInterface $adherentUuid): void
    {
        $this->adherentUuid = $adherentUuid;
    }

    public function getEmail(): string
    {
        return $this->email;
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

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }

    public function setInterests(array $interests): void
    {
        $this->interests = $interests;
    }

    public function getSupervisorTags(): array
    {
        return $this->supervisorTags;
    }

    public function setSupervisorTags(array $supervisorTags): void
    {
        $this->supervisorTags = $supervisorTags;
    }

    public function getCitizenProjects(): ?array
    {
        return $this->citizenProjects;
    }

    public function getCitizenProjectsOrganizer(): ?array
    {
        return $this->citizenProjectsOrganizer;
    }

    public function getSubscriptionTypes(): array
    {
        return $this->subscriptionTypes;
    }

    public function hasSmsSubscriptionType(): bool
    {
        return \in_array(SubscriptionTypeEnum::MILITANT_ACTION_SMS, $this->subscriptionTypes, true);
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
}

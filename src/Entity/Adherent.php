<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\Collection\AdherentCharterCollection;
use AppBundle\Collection\CitizenProjectMembershipCollection;
use AppBundle\Collection\CommitteeMembershipCollection;
use AppBundle\Entity\AdherentCharter\AdherentCharterInterface;
use AppBundle\Entity\BoardMember\BoardMember;
use AppBundle\Exception\AdherentAlreadyEnabledException;
use AppBundle\Exception\AdherentException;
use AppBundle\Exception\AdherentTokenException;
use AppBundle\Geocoder\GeoPointInterface;
use AppBundle\Membership\ActivityPositions;
use AppBundle\Membership\MembershipInterface;
use AppBundle\Membership\MembershipRequest;
use AppBundle\OAuth\Model\User as InMemoryOAuthUser;
use AppBundle\Subscription\SubscriptionTypeEnum;
use AppBundle\ValueObject\Genders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use League\OAuth2\Server\Entities\UserEntityInterface;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={
 *         "get": {
 *             "normalization_context": {"groups": {"idea_vote_read"}},
 *             "method": "GET",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "swagger_context": {
 *                 "summary": "Retrieves an Adherent resource by UUID.",
 *                 "description": "Retrieves an Adherent resource by UUID.",
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Adherent resource.",
 *                         "example": "b4219d47-3138-5efd-9762-2ef9f9495084",
 *                     }
 *                 }
 *             }
 *         }
 *     },
 * )
 *
 * @ORM\Table(name="adherents", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="adherents_uuid_unique", columns="uuid"),
 *     @ORM\UniqueConstraint(name="adherents_email_address_unique", columns="email_address")
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AdherentRepository")
 * @ORM\EntityListeners({"AppBundle\EntityListener\RevokeReferentTeamMemberRolesListener"})
 *
 * @UniqueEntity(fields={"nickname"}, groups={"anonymize"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class Adherent implements UserInterface, UserEntityInterface, GeoPointInterface, EncoderAwareInterface, MembershipInterface, ReferentTaggableEntity, \Serializable, EntityMediaInterface, EquatableInterface
{
    public const ENABLED = 'ENABLED';
    public const TO_DELETE = 'TO_DELETE';
    public const DISABLED = 'DISABLED';

    use EntityCrudTrait;
    use EntityIdentityTrait;
    use EntityPersonNameTrait;
    use EntityPostAddressTrait;
    use LazyCollectionTrait;
    use EntityReferentTagTrait;

    /**
     * @ORM\Column(length=25, unique=true, nullable=true)
     *
     * @Assert\Length(min=3, max=25, groups={"Default", "anonymize"})
     * @Assert\Regex(pattern="/^[a-z0-9 _-]+$/i", message="adherent.nickname.invalid_syntax", groups={"anonymize"})
     * @Assert\Regex(pattern="/^[a-zÀ-ÿ0-9 .!_-]+$/i", message="adherent.nickname.invalid_extended_syntax")
     *
     * @JMS\Groups({"user_profile"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_read", "idea_thread_list_read", "idea_thread_comment_read", "idea_vote_read"})
     */
    private $nickname;

    /**
     * @JMS\Groups({"user_profile"})
     * @JMS\SerializedName("use_nickname")
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $nicknameUsed;

    /**
     * @ORM\Column(nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(nullable=true)
     */
    private $oldPassword;

    /**
     * @ORM\Column(length=6, nullable=true)
     *
     * @JMS\Groups({"adherent_change_diff"})
     */
    private $gender;

    /**
     * @ORM\Column(length=80, nullable=true)
     */
    private $customGender;

    /**
     * @ORM\Column
     *
     * @JMS\Groups({"adherent_change_diff", "user_profile", "public"})
     * @JMS\SerializedName("emailAddress")
     */
    private $emailAddress;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="date", nullable=true)
     *
     * @JMS\Groups({"adherent_change_diff"})
     */
    private $birthdate;

    /**
     * @ORM\Column(length=20, nullable=true)
     */
    private $position;

    /**
     * @ORM\Column(length=10, options={"default": "DISABLED"})
     *
     * @JMS\Groups({"adherent_change_diff"})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registeredAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLoggedAt;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @JMS\Groups({"adherent_change_diff"})
     */
    private $interests = [];

    /**
     * @var SubscriptionType[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="SubscriptionType", cascade={"persist"})
     */
    private $subscriptionTypes;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $legislativeCandidate;

    /**
     * @var ReferentManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ReferentManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $managedArea;

    /**
     * Defines to which referent team the adherent belongs.
     *
     * @var ReferentTeamMember|null
     *
     * @ORM\OneToOne(targetEntity="ReferentTeamMember", mappedBy="member", cascade={"all"}, orphanRemoval=true)
     */
    private $referentTeamMember;

    /**
     * @var CoordinatorManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\CoordinatorManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $coordinatorCitizenProjectArea;

    /**
     * @var CoordinatorManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\CoordinatorManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $coordinatorCommitteeArea;

    /**
     * @var ProcurationManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ProcurationManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $procurationManagedArea;

    /**
     * @var AssessorManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\AssessorManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $assessorManagedArea;

    /**
     * @var AssessorRoleAssociation|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\AssessorRoleAssociation", cascade={"all"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $assessorRole;

    /**
     * @var MunicipalManagerRoleAssociation|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\MunicipalManagerRoleAssociation", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $municipalManagerRole;

    /**
     * @var MunicipalManagerSupervisorRole|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\MunicipalManagerSupervisorRole", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $municipalManagerSupervisorRole;

    /**
     * @var BoardMember|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\BoardMember\BoardMember", mappedBy="adherent", cascade={"all"}, orphanRemoval=true)
     */
    private $boardMember;

    /**
     * @var JecouteManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\JecouteManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $jecouteManagedArea;

    /**
     * @var CommitteeMembership[]|Collection
     *
     * @ORM\OneToMany(targetEntity="CommitteeMembership", mappedBy="adherent", cascade={"remove"})
     */
    private $memberships;

    /**
     * @var CitizenProjectMembership[]|Collection
     *
     * @ORM\OneToMany(targetEntity="CitizenProjectMembership", mappedBy="adherent", cascade={"remove"})
     */
    private $citizenProjectMemberships;

    /**
     * @var CommitteeFeedItem[]|Collection|iterable
     *
     * @ORM\OneToMany(targetEntity="CommitteeFeedItem", mappedBy="author", cascade={"remove"})
     */
    private $committeeFeedItems;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\AdherentTag")
     */
    private $tags;

    /**
     * @var District|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\District", cascade={"persist"})
     */
    private $managedDistrict;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $adherent = false;

    /**
     * @var InMemoryOAuthUser|null
     */
    private $oAuthUser;

    /**
     * @var string[]
     */
    private $roles = [];

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public $localHostEmailsSubscription = false;

    /**
     * @var string[]
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    public $emailsSubscriptions;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    public $comMobile;

    /**
     * Activation token was already sent after 48h of registration
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $remindSent = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @JMS\Groups({"user_profile"})
     */
    private $commentsCguAccepted = false;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $mandates;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\IdeasWorkshop\Idea", mappedBy="author", fetch="EXTRA_LAZY")
     */
    private $ideas;

    /**
     * @var Media|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Media", cascade={"persist"})
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=true)
     */
    private $media;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $displayMedia = true;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?facebook.com\/#", message="legislative_candidate.facebook_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     */
    private $facebookPageUrl;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?twitter.com\/#", message="legislative_candidate.twitter_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     */
    private $twitterPageUrl;

    /**
     * @ORM\Column(length=2, nullable=true)
     */
    private $nationality;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $canaryTester = false;

    /**
     * Mailchimp unsubscribed status
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $emailUnsubscribed = false;

    /**
     * Mailchimp unsubscribed date
     *
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $emailUnsubscribedAt;

    /**
     * @var MunicipalChiefManagedArea|null
     *
     * @Assert\Valid
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\MunicipalChiefManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $municipalChiefManagedArea;

    /**
     * Access to external services regarding printing
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $printPrivilege = false;

    /**
     * @var Collection|AdherentCharterInterface[]
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\AdherentCharter\AbstractAdherentCharter", mappedBy="adherent", cascade={"all"})
     */
    private $charters;

    /**
     * @var SenatorArea|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\SenatorArea", cascade={"all"}, orphanRemoval=true)
     */
    private $senatorArea;

    /**
     * @var ConsularManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ConsularManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $consularManagedArea;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $electionResultsReporter = false;

    public function __construct()
    {
        $this->memberships = new ArrayCollection();
        $this->citizenProjectMemberships = new ArrayCollection();
        $this->subscriptionTypes = new ArrayCollection();
        $this->ideas = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->charters = new AdherentCharterCollection();
    }

    public static function create(
        UuidInterface $uuid,
        string $emailAddress,
        string $password,
        ?string $gender,
        string $firstName,
        string $lastName,
        ?\DateTime $birthDate,
        ?string $position,
        PostAddress $postAddress,
        PhoneNumber $phone = null,
        string $nickname = null,
        bool $nicknameUsed = false,
        string $status = self::DISABLED,
        string $registeredAt = 'now',
        ?array $tags = [],
        ?array $referentTags = [],
        array $mandates = null,
        string $nationality = null,
        string $customGender = null
    ) {
        $adherent = new self();

        $adherent->uuid = $uuid;
        $adherent->password = $password;
        $adherent->gender = $gender;
        $adherent->firstName = $firstName;
        $adherent->lastName = $lastName;
        $adherent->nickname = $nickname;
        $adherent->nicknameUsed = $nicknameUsed;
        $adherent->emailAddress = $emailAddress;
        $adherent->birthdate = $birthDate;
        $adherent->position = $position;
        $adherent->postAddress = $postAddress;
        $adherent->phone = $phone;
        $adherent->status = $status;
        $adherent->legislativeCandidate = false;
        $adherent->registeredAt = new \DateTime($registeredAt);
        $adherent->tags = new ArrayCollection($tags);
        $adherent->referentTags = new ArrayCollection($referentTags);
        $adherent->mandates = $mandates;
        $adherent->nationality = $nationality;
        $adherent->customGender = $customGender;

        return $adherent;
    }

    public function getIdentifier()
    {
        return $this->getUuidAsString();
    }

    public static function createUuid(string $email): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, $email);
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("uuid")
     * @JMS\Groups({"user_profile", "public"})
     */
    public function getUuidAsString(): string
    {
        return $this->getUuid()->toString();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("subscriptionExternalIds")
     * @JMS\Groups({"public"})
     */
    public function getSubscriptionExternalIds(): array
    {
        return array_values(array_filter(array_map(function (SubscriptionType $subscription) {
            return $subscription->getExternalId();
        }, $this->getSubscriptionTypes())));
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("elected")
     * @JMS\Groups({"user_profile"})
     */
    public function isElected(): bool
    {
        return $this->getMandates() && \count($this->getMandates()) > 0;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("larem")
     * @JMS\Groups({"user_profile"})
     */
    public function isLaREM(): bool
    {
        return $this->getTags()->filter(function (AdherentTag $tag) {
            return AdherentTagEnum::LAREM === $tag->getName();
        })->count() > 0;
    }

    public function getRoles()
    {
        $roles = ['ROLE_USER'];

        if ($this->isAdherent()) {
            $roles[] = 'ROLE_ADHERENT';
        }

        if ($this->isReferent()) {
            $roles[] = 'ROLE_REFERENT';
        }

        if ($this->isCoReferent()) {
            $roles[] = 'ROLE_COREFERENT';
        }

        if ($this->isDeputy()) {
            $roles[] = 'ROLE_DEPUTY';
        }

        if ($this->isSenator()) {
            $roles[] = 'ROLE_SENATOR';
        }

        if ($this->isConsular()) {
            $roles[] = 'ROLE_CONSULAR';
        }

        if ($this->isAdherentMessageRedactor()) {
            $roles[] = 'ROLE_MESSAGE_REDACTOR';
        }

        if ($this->hasFormationSpaceAccess()) {
            $roles[] = 'ROLE_FORMATION_SPACE';
        }

        if ($this->isCoordinator()) {
            $roles[] = 'ROLE_COORDINATOR';
        }

        if ($this->isCoordinatorCitizenProjectSector()) {
            $roles[] = 'ROLE_COORDINATOR_CITIZEN_PROJECT';
        }

        if ($this->isCoordinatorCommitteeSector()) {
            $roles[] = 'ROLE_COORDINATOR_COMMITTEE';
        }

        if ($this->isHost()) {
            $roles[] = 'ROLE_HOST';
        }

        if ($this->isSupervisor()) {
            $roles[] = 'ROLE_SUPERVISOR';
        }

        if ($this->isProcurationManager()) {
            $roles[] = 'ROLE_PROCURATION_MANAGER';
        }

        if ($this->isAssessorManager()) {
            $roles[] = 'ROLE_ASSESSOR_MANAGER';
        }

        if ($this->isAssessor()) {
            $roles[] = 'ROLE_ASSESSOR';
        }

        if ($this->isMunicipalManager()) {
            $roles[] = 'ROLE_MUNICIPAL_MANAGER';
        }

        if ($this->isMunicipalManagerSupervisor()) {
            $roles[] = 'ROLE_MUNICIPAL_MANAGER_SUPERVISOR';
        }

        if ($this->isJecouteManager()) {
            $roles[] = 'ROLE_JECOUTE_MANAGER';
        }

        if ($this->legislativeCandidate) {
            $roles[] = 'ROLE_LEGISLATIVE_CANDIDATE';
        }

        if ($this->isBoardMember()) {
            $roles[] = 'ROLE_BOARD_MEMBER';
        }

        if ($this->isCitizenProjectAdministrator()) {
            $roles[] = 'ROLE_CITIZEN_PROJECT_ADMINISTRATOR';
        }

        if ($this->canaryTester) {
            $roles[] = 'ROLE_CANARY_TESTER';
        }

        if ($this->isMunicipalChief()) {
            $roles[] = 'ROLE_MUNICIPAL_CHIEF';
        }

        if ($this->hasPrintPrivilege()) {
            $roles[] = 'ROLE_PRINT_PRIVILEGE';
        }

        if ($this->isElectionResultsReporter()) {
            $roles[] = 'ROLE_ELECTION_RESULTS_REPORTER';
        }

        return array_merge($roles, $this->roles);
    }

    public function addRoles(array $roles): void
    {
        foreach ($roles as $role) {
            $this->roles[] = $role;
        }
    }

    public function getType(): string
    {
        if ($this->isReferent()) {
            return 'REFERENT';
        }

        if ($this->isHost()) {
            return 'HOST';
        }

        return 'ADHERENT';
    }

    public function hasAdvancedPrivileges(): bool
    {
        return $this->isReferent()
            || $this->isCoordinator()
            || $this->isProcurationManager()
            || $this->isAssessorManager()
            || $this->isAssessor()
            || $this->isJecouteManager()
            || $this->isHost()
            || $this->isCitizenProjectAdministrator()
            || $this->isBoardMember()
            || $this->isDeputy()
            || $this->isSenator()
            || $this->isMunicipalChief()
            || $this->isMunicipalManager()
            || $this->isElectionResultsReporter()
            || $this->isMunicipalManagerSupervisor()
        ;
    }

    public function getPassword()
    {
        return !$this->password ? $this->oldPassword : $this->password;
    }

    public function hasLegacyPassword(): bool
    {
        return null !== $this->oldPassword;
    }

    public function getEncoderName(): ?string
    {
        if ($this->hasLegacyPassword()) {
            return 'legacy_encoder';
        }

        return null;
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
        return $this->emailAddress;
    }

    public function eraseCredentials()
    {
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getCustomGender(): ?string
    {
        return $this->customGender;
    }

    public function isForeignResident(): bool
    {
        return 'FR' !== strtoupper($this->getCountry());
    }

    public function isFemale(): bool
    {
        return Genders::FEMALE === $this->gender;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    /**
     * @SymfonySerializer\Groups({"export"})
     */
    public function getAge(): ?int
    {
        return $this->birthdate ? $this->birthdate->diff(new \DateTime())->y : null;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): void
    {
        if (!ActivityPositions::exists($position)) {
            throw new \InvalidArgumentException(sprintf('Invalid position "%s", known positions are "%s".', $position, implode('", "', ActivityPositions::ALL)));
        }

        $this->position = $position;
    }

    public function setNickname(?string $nickname): void
    {
        $this->nickname = $nickname;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function isEnabled(): bool
    {
        return self::ENABLED === $this->status;
    }

    public function getActivatedAt(): ?\DateTime
    {
        return $this->activatedAt;
    }

    public function changePassword(string $newPassword): void
    {
        $this->password = $newPassword;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"adherent_change_diff"})
     * @JMS\SerializedName("subscriptionTypeCodes")
     */
    public function getSubscriptionTypeCodes(): array
    {
        return array_values(array_map(static function (SubscriptionType $type) {
            return $type->getCode();
        }, $this->subscriptionTypes->toArray()));
    }

    /**
     * @return SubscriptionType[]
     */
    public function getSubscriptionTypes(): array
    {
        return $this->subscriptionTypes->toArray();
    }

    public function hasSubscriptionType(string $code): bool
    {
        return $this->subscriptionTypes->exists(function (int $index, SubscriptionType $type) use ($code) {
            return $type->getCode() === $code;
        });
    }

    public function hasSubscribedLocalHostEmails(): bool
    {
        return $this->hasSubscriptionType(SubscriptionTypeEnum::LOCAL_HOST_EMAIL);
    }

    public function hasSmsSubscriptionType(): bool
    {
        return $this->hasSubscriptionType(SubscriptionTypeEnum::MILITANT_ACTION_SMS);
    }

    /**
     * Activates the Adherent account with the provided activation token.
     *
     * @throws AdherentException
     * @throws AdherentTokenException
     */
    public function activate(AdherentActivationToken $token, string $timestamp = 'now'): void
    {
        if ($this->activatedAt) {
            throw new AdherentAlreadyEnabledException($this->uuid);
        }

        $token->consume($this);

        $this->status = self::ENABLED;
        $this->activatedAt = new \DateTime($timestamp);
    }

    /**
     * Resets the Adherent password using a reset pasword token.
     *
     * @throws \InvalidArgumentException
     * @throws AdherentException
     * @throws AdherentTokenException
     */
    public function resetPassword(AdherentResetPasswordToken $token): void
    {
        if (!$newPassword = $token->getNewPassword()) {
            throw new \InvalidArgumentException('Token must have a new password.');
        }

        $token->consume($this);

        $this->clearOldPassword();
        $this->password = $newPassword;
    }

    public function changeEmail(AdherentChangeEmailToken $token): void
    {
        if (!$token->getEmail()) {
            throw new \InvalidArgumentException('Token must have a new email.');
        }

        $token->consume($this);

        $this->emailAddress = $token->getEmail();
    }

    public function clearOldPassword(): void
    {
        $this->oldPassword = null;
    }

    public function migratePassword(string $newEncodedPassword): void
    {
        $this->password = $newEncodedPassword;
    }

    /**
     * Records the adherent last login date and time.
     *
     * @param string|int $timestamp a valid date representation as a string or integer
     */
    public function recordLastLoginTime($timestamp = 'now'): void
    {
        $this->lastLoggedAt = new \DateTime($timestamp);
    }

    /**
     * Returns the last login date and time of this adherent.
     */
    public function getLastLoggedAt(): ?\DateTime
    {
        return $this->lastLoggedAt;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }

    public function setInterests(array $interests): void
    {
        $this->interests = $interests;
    }

    public function updateMembership(MembershipRequest $membership, PostAddress $postAddress): void
    {
        $this->customGender = $membership->customGender;
        $this->gender = $membership->gender;
        $this->firstName = $membership->firstName;
        $this->lastName = $membership->lastName;
        $this->birthdate = $membership->getBirthdate();
        $this->position = $membership->position;
        $this->phone = $membership->getPhone();
        $this->emailAddress = $membership->getEmailAddress();
        $this->mandates = $membership->getMandates();
        $this->nationality = $membership->nationality;

        if (!$this->postAddress->equals($postAddress)) {
            $this->postAddress = $postAddress;
        }
    }

    /**
     * Joins a committee as a SUPERVISOR privileged person.
     */
    public function superviseCommittee(Committee $committee, string $subscriptionDate = 'now'): CommitteeMembership
    {
        return $this->joinCommittee($committee, CommitteeMembership::COMMITTEE_SUPERVISOR, $subscriptionDate);
    }

    /**
     * Joins a committee as a HOST privileged person.
     */
    public function hostCommittee(Committee $committee, string $subscriptionDate = 'now'): CommitteeMembership
    {
        return $this->joinCommittee($committee, CommitteeMembership::COMMITTEE_HOST, $subscriptionDate);
    }

    /**
     * Joins a committee as a simple FOLLOWER privileged person.
     */
    public function followCommittee(Committee $committee, string $subscriptionDate = 'now'): CommitteeMembership
    {
        return $this->joinCommittee($committee, CommitteeMembership::COMMITTEE_FOLLOWER, $subscriptionDate);
    }

    private function joinCommittee(
        Committee $committee,
        string $privilege,
        string $subscriptionDate
    ): CommitteeMembership {
        $committee->incrementMembersCount();

        return CommitteeMembership::createForAdherent($committee, $this, $privilege, $subscriptionDate);
    }

    /**
     * Joins a citizen project as a ADMINISTRATOR privileged person.
     */
    public function administrateCitizenProject(
        CitizenProject $citizenProject,
        string $subscriptionDate = 'now'
    ): CitizenProjectMembership {
        return $this->joinCitizenProject($citizenProject, CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR, $subscriptionDate);
    }

    /**
     * Joins a citizen project as a simple FOLLOWER privileged person.
     */
    public function followCitizenProject(
        CitizenProject $citizenProject,
        string $subscriptionDate = 'now'
    ): CitizenProjectMembership {
        return $this->joinCitizenProject($citizenProject, CitizenProjectMembership::CITIZEN_PROJECT_FOLLOWER, $subscriptionDate);
    }

    private function joinCitizenProject(
        CitizenProject $citizenProject,
        string $privilege,
        string $subscriptionDate
    ): CitizenProjectMembership {
        $citizenProject->incrementMembersCount();

        $memberShip = CitizenProjectMembership::createForAdherent($citizenProject, $this, $privilege, $subscriptionDate);

        $this->citizenProjectMemberships->add($memberShip);

        return $memberShip;
    }

    public function getPostAddress(): PostAddress
    {
        return $this->postAddress;
    }

    /**
     * Returns whether or not the current adherent is the same as the given one.
     */
    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @SymfonySerializer\Groups({"export"})
     */
    public function getRegisteredAt(): ?\DateTime
    {
        return $this->registeredAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function getManagedArea(): ?ReferentManagedArea
    {
        return $this->managedArea;
    }

    public function setManagedArea(ReferentManagedArea $managedArea): void
    {
        $this->managedArea = $managedArea;
    }

    public function getReferentTeamMember(): ?ReferentTeamMember
    {
        return $this->referentTeamMember;
    }

    public function setReferentTeamMember(?ReferentTeamMember $referentTeam): void
    {
        if ($referentTeam) {
            $referentTeam->setMember($this);
        }
        $this->referentTeamMember = $referentTeam;
    }

    public function getReferentOfReferentTeam(): ?Adherent
    {
        return $this->referentTeamMember ? $this->referentTeamMember->getReferent() : null;
    }

    public function getMemberOfReferentTeam(): ?Adherent
    {
        return $this->referentTeamMember ? $this->referentTeamMember->getMember() : null;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("managedAreaTagCodes")
     * @JMS\Groups({"referent"})
     *
     * @return string[]
     */
    public function getManagedAreaTagCodes(): array
    {
        return $this->getManagedArea()
            ? $this->getManagedArea()->getReferentTagCodes()
            : []
        ;
    }

    public function getProcurationManagedArea(): ?ProcurationManagedArea
    {
        return $this->procurationManagedArea;
    }

    public function setProcurationManagedArea(ProcurationManagedArea $procurationManagedArea = null): void
    {
        $this->procurationManagedArea = $procurationManagedArea;
    }

    public function getAssessorManagedArea(): ?AssessorManagedArea
    {
        return $this->assessorManagedArea;
    }

    public function setAssessorManagedArea(AssessorManagedArea $assessorManagedArea = null): void
    {
        $this->assessorManagedArea = $assessorManagedArea;
    }

    public function getAssessorRole(): ?AssessorRoleAssociation
    {
        return $this->assessorRole;
    }

    public function setAssessorRole(?AssessorRoleAssociation $assessorRole): void
    {
        $this->assessorRole = $assessorRole;
    }

    public function getMunicipalManagerRole(): ?MunicipalManagerRoleAssociation
    {
        return $this->municipalManagerRole;
    }

    public function setMunicipalManagerRole(?MunicipalManagerRoleAssociation $municipalManagerRole): void
    {
        $this->municipalManagerRole = $municipalManagerRole;
    }

    public function getMunicipalManagerSupervisorRole(): ?MunicipalManagerSupervisorRole
    {
        return $this->municipalManagerSupervisorRole;
    }

    public function setMunicipalManagerSupervisorRole(
        ?MunicipalManagerSupervisorRole $municipalManagerSupervisorRole
    ): void {
        $this->municipalManagerSupervisorRole = $municipalManagerSupervisorRole;
    }

    public function revokeMunicipalManagerSupervisorRole(): void
    {
        $this->municipalManagerSupervisorRole = null;
    }

    public function isMunicipalManagerSupervisor(): bool
    {
        return $this->municipalManagerSupervisorRole instanceof MunicipalManagerSupervisorRole;
    }

    public function getBoardMember(): ?BoardMember
    {
        return $this->boardMember;
    }

    public function setBoardMember(string $area, iterable $roles): void
    {
        if (!$this->boardMember) {
            $this->boardMember = new BoardMember();
            $this->boardMember->setAdherent($this);
        }

        $this->boardMember->setArea($area);
        $this->boardMember->setRoles($roles);
    }

    public function isBoardMember(): bool
    {
        return $this->boardMember instanceof BoardMember
            && !empty($this->boardMember->getArea()) && !empty($this->boardMember->getRoles());
    }

    public function revokeBoardMember(): void
    {
        if (!$this->boardMember) {
            return;
        }

        $this->boardMember->revoke();
        $this->boardMember = null;
    }

    public function setReferent(array $tags, string $markerLatitude = null, string $markerLongitude = null): void
    {
        $this->managedArea = new ReferentManagedArea($tags, $markerLatitude, $markerLongitude);
    }

    public function isReferent(): bool
    {
        return $this->managedArea instanceof ReferentManagedArea
            && !$this->managedArea->getTags()->isEmpty();
    }

    public function isCoReferent(): bool
    {
        return $this->referentTeamMember instanceof ReferentTeamMember;
    }

    public function revokeReferent(): void
    {
        $this->managedArea = null;
    }

    public function revokeAssessorManager(): void
    {
        $this->assessorManagedArea = null;
    }

    public function revokeProcurationManager(): void
    {
        $this->procurationManagedArea = null;
    }

    public function revokeJecouteManager(): void
    {
        $this->jecouteManagedArea = null;
    }

    public function getManagedAreaMarkerLatitude(): ?string
    {
        if (!$this->managedArea) {
            return '';
        }

        return $this->managedArea->getMarkerLatitude();
    }

    public function getManagedAreaMarkerLongitude(): ?string
    {
        if (!$this->managedArea) {
            return '';
        }

        return $this->managedArea->getMarkerLongitude();
    }

    public function isProcurationManager(): bool
    {
        return $this->procurationManagedArea instanceof ProcurationManagedArea && !empty($this->procurationManagedArea->getCodes());
    }

    public function isAssessorManager(): bool
    {
        return $this->assessorManagedArea instanceof AssessorManagedArea && !empty($this->assessorManagedArea->getCodes());
    }

    public function isAssessor(): bool
    {
        return !empty($this->assessorRole);
    }

    public function isMunicipalManager(): bool
    {
        return !empty($this->municipalManagerRole);
    }

    public function revokeMunicipalManager(): void
    {
        $this->municipalManagerRole = null;
    }

    public function canBeProxy(): bool
    {
        return $this->isReferent() || $this->isProcurationManager();
    }

    public function getProcurationManagedAreaCodesAsString(): ?string
    {
        if (!$this->procurationManagedArea) {
            return '';
        }

        return $this->procurationManagedArea->getCodesAsString();
    }

    public function setProcurationManagedAreaCodesAsString(string $codes = null): void
    {
        if (!$this->procurationManagedArea) {
            $this->procurationManagedArea = new ProcurationManagedArea();
        }

        $this->procurationManagedArea->setCodesAsString($codes);
    }

    public function getAssessorManagedAreaCodesAsString(): ?string
    {
        if (!$this->assessorManagedArea) {
            return '';
        }

        return $this->assessorManagedArea->getCodesAsString();
    }

    public function setAssessorManagedAreaCodesAsString(string $codes = null): void
    {
        if (!$this->assessorManagedArea) {
            $this->assessorManagedArea = new AssessorManagedArea();
        }

        $this->assessorManagedArea->setCodesAsString($codes);
    }

    public function isCoordinator(): bool
    {
        return $this->isCoordinatorCommitteeSector() || $this->isCoordinatorCitizenProjectSector();
    }

    public function isCoordinatorCitizenProjectSector(): bool
    {
        return $this->coordinatorCitizenProjectArea && $this->coordinatorCitizenProjectArea->getCodes();
    }

    public function isCoordinatorCommitteeSector(): bool
    {
        return $this->coordinatorCommitteeArea && $this->coordinatorCommitteeArea->getCodes();
    }

    public function getJecouteManagedAreaCodesAsString(): ?string
    {
        if (!$this->jecouteManagedArea) {
            return '';
        }

        return $this->jecouteManagedArea->getCodesAsString();
    }

    public function getJecouteManagedArea(): ?JecouteManagedArea
    {
        return $this->jecouteManagedArea;
    }

    public function setJecouteManagedAreaCodesAsString(string $codes = null): void
    {
        if (!$this->jecouteManagedArea) {
            $this->jecouteManagedArea = new JecouteManagedArea();
        }

        $this->jecouteManagedArea->setCodesAsString($codes);
    }

    public function isJecouteManager(): bool
    {
        return $this->jecouteManagedArea instanceof JecouteManagedArea && !empty($this->jecouteManagedArea->getCodes());
    }

    /**
     * @return CommitteeMembership[]|CommitteeMembershipCollection
     */
    public function getMemberships(): CommitteeMembershipCollection
    {
        if (!$this->memberships instanceof CommitteeMembershipCollection) {
            $this->memberships = new CommitteeMembershipCollection($this->memberships->toArray());
        }

        return $this->memberships;
    }

    public function hasLoadedMemberships(): bool
    {
        return $this->isCollectionLoaded($this->memberships);
    }

    public function getMembershipFor(Committee $committee): ?CommitteeMembership
    {
        foreach ($this->memberships as $membership) {
            if ($membership->matches($this, $committee)) {
                return $membership;
            }
        }

        return null;
    }

    /**
     * @return CitizenProjectMembership[]|CitizenProjectMembershipCollection
     */
    public function getCitizenProjectMemberships($withoutRefused = false): CitizenProjectMembershipCollection
    {
        if (!$this->citizenProjectMemberships instanceof CitizenProjectMembershipCollection) {
            $this->citizenProjectMemberships = new CitizenProjectMembershipCollection($this->citizenProjectMemberships->toArray());
        }

        if ($withoutRefused) {
            return $this->citizenProjectMemberships->filterRefusedProjects();
        }

        return $this->citizenProjectMemberships;
    }

    public function hasLoadedCitizenProjectMemberships(): bool
    {
        return $this->isCollectionLoaded($this->citizenProjectMemberships);
    }

    public function getCitizenProjectMembershipFor(CitizenProject $citizenProject): ?CitizenProjectMembership
    {
        foreach ($this->citizenProjectMemberships as $citizenProjectMembership) {
            if ($citizenProjectMembership->matches($this, $citizenProject)) {
                return $citizenProjectMembership;
            }
        }

        return null;
    }

    public function isBasicAdherent(): bool
    {
        return $this->isAdherent() && !$this->isHost() && !$this->isReferent() && !$this->isBoardMember() && !$this->isDeputy();
    }

    public function isHost(): bool
    {
        return $this->getMemberships()->countCommitteeHostMemberships() >= 1;
    }

    public function isHostOnly(): bool
    {
        return $this->getMemberships()->getCommitteeHostMemberships(CommitteeMembershipCollection::EXCLUDE_SUPERVISORS)->count() >= 1;
    }

    public function isHostOf(Committee $committee): bool
    {
        if (!$membership = $this->getMembershipFor($committee)) {
            return false;
        }

        return $membership->canHostCommittee();
    }

    public function isCitizenProjectAdministrator(): bool
    {
        return $this->getCitizenProjectMemberships()->countCitizenProjectAdministratorMemberships() >= 1;
    }

    public function isAdministratorOf(CitizenProject $citizenProject): bool
    {
        if (!$membership = $this->getCitizenProjectMembershipFor($citizenProject)) {
            return false;
        }

        return $membership->canAdministrateCitizenProject();
    }

    public function isSupervisor(): bool
    {
        return $this->getMemberships()->countCommitteeSupervisorMemberships() >= 1;
    }

    public function isSupervisorOf(Committee $committee): bool
    {
        if (!$membership = $this->getMembershipFor($committee)) {
            return false;
        }

        return $membership->isSupervisor();
    }

    public function isLegislativeCandidate(): bool
    {
        return $this->legislativeCandidate;
    }

    public function setLegislativeCandidate(bool $candidate): void
    {
        $this->legislativeCandidate = $candidate;
    }

    public function isNicknameUsed(): bool
    {
        return $this->nicknameUsed;
    }

    public function setNicknameUsed(bool $nicknameUsed): void
    {
        $this->nicknameUsed = $nicknameUsed;
    }

    public function addSubscriptionType(SubscriptionType $type): void
    {
        if (!$this->subscriptionTypes->contains($type)) {
            $this->subscriptionTypes->add($type);
        }
    }

    public function removeSubscriptionType(SubscriptionType $type): void
    {
        $this->subscriptionTypes->removeElement($type);
    }

    public function removeSubscriptionTypeByCode(string $code): void
    {
        foreach ($this->subscriptionTypes as $type) {
            if ($code === $type->getCode()) {
                $this->removeSubscriptionType($type);
            }
        }
    }

    public function setSubscriptionTypes(array $subscriptionTypes)
    {
        $this->subscriptionTypes = new ArrayCollection();
        foreach ($subscriptionTypes as $type) {
            $this->addSubscriptionType($type);
        }
    }

    public function getCommitteeFeedItems(): iterable
    {
        return $this->committeeFeedItems;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function setTags(iterable $tags): void
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
    }

    public function addTag(AdherentTag $adherentTag): void
    {
        if (!$this->tags->contains($adherentTag)) {
            $this->tags->add($adherentTag);
        }
    }

    public function removeTag(AdherentTag $adherentTag): void
    {
        $this->tags->removeElement($adherentTag);
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"adherent_change_diff"})
     * @JMS\SerializedName("referentTagCodes")
     */
    public function getReferentTagCodes(): array
    {
        return array_map(function (ReferentTag $tag) { return $tag->getCode(); }, $this->referentTags->toArray());
    }

    public function hasCitizenProjectHostEmailSubscription(): bool
    {
        return $this->hasSubscriptionType(SubscriptionTypeEnum::CITIZEN_PROJECT_HOST_EMAIL);
    }

    public function getCoordinatorCitizenProjectArea(): ?CoordinatorManagedArea
    {
        return $this->coordinatorCitizenProjectArea;
    }

    public function setCoordinatorCitizenProjectArea(?CoordinatorManagedArea $coordinatorCitizenProjectArea): void
    {
        $this->coordinatorCitizenProjectArea = $coordinatorCitizenProjectArea;
    }

    public function getCoordinatorCommitteeArea(): ?CoordinatorManagedArea
    {
        return $this->coordinatorCommitteeArea;
    }

    public function setCoordinatorCommitteeArea(?CoordinatorManagedArea $coordinatorCommitteeArea): void
    {
        $this->coordinatorCommitteeArea = $coordinatorCommitteeArea;
    }

    public function getManagedDistrict(): ?District
    {
        return $this->managedDistrict;
    }

    public function setManagedDistrict(?District $district): void
    {
        $this->managedDistrict = $district;
    }

    public function isDeputy(): bool
    {
        return $this->managedDistrict instanceof District;
    }

    public function isAdherent(): bool
    {
        return $this->adherent;
    }

    public function isUser(): bool
    {
        return !$this->isAdherent();
    }

    public function join(): void
    {
        $this->adherent = true;
    }

    public function getOAuthUser(): InMemoryOAuthUser
    {
        if (!$this->oAuthUser) {
            $this->oAuthUser = new InMemoryOAuthUser($this->uuid);
        }

        return $this->oAuthUser;
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->emailAddress,
            $this->password,
            $this->getRoles(),
        ]);
    }

    public function unserialize($serialized)
    {
        list($this->id, $this->emailAddress, $this->password, $this->roles) = unserialize($serialized);
    }

    public function setRemindSent(bool $remindSent): void
    {
        $this->remindSent = $remindSent;
    }

    public function getMandates(): ?array
    {
        return $this->mandates;
    }

    public function setMandates(?array $mandates): void
    {
        $this->mandates = $mandates;
    }

    public function hasMandate(): bool
    {
        return !empty($this->mandates);
    }

    public function getSenatorArea(): ?SenatorArea
    {
        return $this->senatorArea;
    }

    public function setSenatorArea(?SenatorArea $senatorArea): void
    {
        $this->senatorArea = $senatorArea;
    }

    public function getConsularManagedArea(): ?ConsularManagedArea
    {
        return $this->consularManagedArea;
    }

    public function setConsularManagedArea(?ConsularManagedArea $consularManagedArea): void
    {
        $this->consularManagedArea = $consularManagedArea;
    }

    public function isCommentsCguAccepted(): bool
    {
        return $this->commentsCguAccepted;
    }

    public function setCommentsCguAccepted(bool $commentsCguAccepted): void
    {
        $this->commentsCguAccepted = $commentsCguAccepted;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(Media $media = null): void
    {
        $this->media = $media;
    }

    public function displayMedia(): bool
    {
        return $this->displayMedia;
    }

    public function setDisplayMedia(bool $displayMedia): void
    {
        $this->displayMedia = $displayMedia;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    /**
     * @Algolia\Attribute(algoliaName="address_city")
     *
     * @JMS\Groups({"adherent_change_diff", "public"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("city")
     *
     * @SymfonySerializer\Groups({"export"})
     */
    public function getCityName(): ?string
    {
        return $this->postAddress->getCityName();
    }

    public function isEmailUnsubscribed(): bool
    {
        return $this->emailUnsubscribed;
    }

    public function setEmailUnsubscribed(bool $value): void
    {
        if ($value) {
            $this->emailUnsubscribedAt = new \DateTime();
        }

        $this->emailUnsubscribed = $value;
    }

    public function isAdherentMessageRedactor(): bool
    {
        return $this->isReferent()
            || $this->isDeputy()
            || $this->isHost()
            || $this->isSupervisor()
            || $this->isCitizenProjectAdministrator()
            || $this->isMunicipalChief()
        ;
    }

    public function isMunicipalChief(): bool
    {
        return $this->municipalChiefManagedArea instanceof MunicipalChiefManagedArea
            && $this->municipalChiefManagedArea->getInseeCode();
    }

    public function getMunicipalChiefManagedArea(): ?MunicipalChiefManagedArea
    {
        return $this->municipalChiefManagedArea;
    }

    public function setMunicipalChiefManagedArea(MunicipalChiefManagedArea $municipalChiefManagedArea = null): void
    {
        $this->municipalChiefManagedArea = $municipalChiefManagedArea;
    }

    public function __clone()
    {
        $this->subscriptionTypes = new ArrayCollection($this->subscriptionTypes->toArray());
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"user_profile"})
     */
    public function getDetailedRoles(): array
    {
        $roles = [];

        if ($this->isReferent()) {
            $roles[] = [
                'label' => 'ROLE_REFERENT',
                'codes' => $this->getManagedAreaTagCodes(),
            ];
        }

        if ($this->isMunicipalChief()) {
            $roles[] = [
                'label' => 'ROLE_MUNICIPAL_CHIEF',
                'codes' => [$this->municipalChiefManagedArea->getInseeCode()],
            ];
        }

        if ($this->hasPrintPrivilege()) {
            $roles[] = [
                'label' => 'ROLE_PRINT_PRIVILEGE',
            ];
        }

        return $roles;
    }

    public function hasPrintPrivilege(): bool
    {
        return $this->printPrivilege;
    }

    public function setPrintPrivilege(bool $printPrivilege): void
    {
        $this->printPrivilege = $printPrivilege;
    }

    public function hasFormationSpaceAccess(): bool
    {
        return
            $this->isHost()
            || $this->isSupervisor()
            || $this->isReferent()
            || $this->isMunicipalChief()
        ;
    }

    public function getCharters(): AdherentCharterCollection
    {
        if (!$this->charters instanceof AdherentCharterCollection) {
            $this->charters = new AdherentCharterCollection($this->charters->toArray());
        }

        return $this->charters;
    }

    public function addCharter(AdherentCharterInterface $charter): void
    {
        if (!$this->charters->contains($charter)) {
            $charter->setAdherent($this);
            $this->charters->add($charter);
        }
    }

    public function isSenator(): bool
    {
        return !empty($this->senatorArea);
    }

    public function isConsular(): bool
    {
        return !empty($this->consularManagedArea);
    }

    /**
     * @param UserInterface|self $user
     */
    public function isEqualTo(UserInterface $user)
    {
        return $this->id === $user->getId() && $this->roles === $user->getRoles();
    }

    public function isElectionResultsReporter(): bool
    {
        return $this->electionResultsReporter;
    }

    public function setElectionResultsReporter(bool $electionResultsReporter): void
    {
        $this->electionResultsReporter = $electionResultsReporter;
    }

    public function markAsToDelete(): void
    {
        $this->status = self::TO_DELETE;
    }

    public function isToDelete(): bool
    {
        return self::TO_DELETE === $this->status;
    }
}

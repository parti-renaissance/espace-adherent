<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Address\AddressInterface;
use App\Address\PostAddressFactory;
use App\Adherent\Contribution\ContributionAmountUtils;
use App\Adherent\LastLoginGroupEnum;
use App\Adherent\MandateTypeEnum;
use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TranslatedTagInterface;
use App\AdherentProfile\AdherentProfile;
use App\Adhesion\AdhesionStepEnum;
use App\Adhesion\Request\MembershipRequest;
use App\AppSession\SessionStatusEnum;
use App\Collection\AdherentCharterCollection;
use App\Collection\CertificationRequestCollection;
use App\Collection\ZoneCollection;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Controller\Api\Mailchimp\SendResubscribeEmailController;
use App\Controller\Api\UpdateImageController;
use App\Entity\AdherentCharter\AdherentCharterInterface;
use App\Entity\AdherentMandate\AbstractAdherentMandate;
use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\Campus\Registration;
use App\Entity\Contribution\Contribution;
use App\Entity\Contribution\Payment;
use App\Entity\Contribution\RevenueDeclaration;
use App\Entity\Filesystem\FilePermissionEnum;
use App\Entity\Geo\Zone;
use App\Entity\ManagedArea\CandidateManagedArea;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\DelegatedAccessEnum;
use App\Entity\OAuth\Client;
use App\Entity\Team\Member;
use App\Enum\CivilityEnum;
use App\Exception\AdherentAlreadyEnabledException;
use App\Exception\AdherentException;
use App\Exception\AdherentTokenException;
use App\Geocoder\GeoPointInterface;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Mailchimp\Contact\MailchimpCleanableContactInterface;
use App\Membership\ActivityPositionsEnum;
use App\Membership\MembershipRequest\MembershipInterface;
use App\Membership\MembershipSourceEnum;
use App\OAuth\Model\User as InMemoryOAuthUser;
use App\Renaissance\Membership\Admin\AdherentCreateCommand;
use App\Renaissance\Membership\Admin\MembershipTypeEnum;
use App\Repository\AdherentRepository;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;
use App\Utils\AreaUtils;
use App\Validator\UniqueMembership;
use App\Validator\ZoneBasedRoles as AssertZoneBasedRoles;
use App\ValueObject\Genders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use League\OAuth2\Server\Entities\UserEntityInterface;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/adherents/{uuid}/elect',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "(is_granted('REQUEST_SCOPE_GRANTED', 'elected_representative')) or (is_granted('ROLE_OAUTH_SCOPE_READ:PROFILE') and object === user)"
        ),
        new Post(
            uriTemplate: '/adherents/{uuid}/send-resubscribe-email',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: SendResubscribeEmailController::class,
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'contacts')",
            deserialize: false,
        ),
        new Put(
            uriTemplate: '/adherents/{uuid}/elect',
            requirements: ['uuid' => '%pattern_uuid%'],
            denormalizationContext: ['groups' => ['adherent_elect_update']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'elected_representative')",
            validationContext: ['groups' => ['adherent_elect_update']]
        ),
        new HttpOperation(
            method: 'POST|DELETE',
            uriTemplate: '/profile/{uuid}/image',
            controller: UpdateImageController::class,
            security: "is_granted('ROLE_OAUTH_SCOPE_WRITE:PROFILE') and object === user",
            deserialize: false,
        ),
    ],
    routePrefix: '/v3',
    normalizationContext: ['groups' => ['adherent_elect_read']],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'contacts')"
)]
#[ORM\Entity(repositoryClass: AdherentRepository::class)]
#[ORM\Index(columns: ['tags'], options: ['lengths' => [512]])]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['mailchimp_status'])]
#[ORM\Table(name: 'adherents')]
#[UniqueEntity(fields: ['nickname'], groups: ['anonymize'])]
#[UniqueMembership(groups: ['Admin'])]
class Adherent implements UserInterface, UserEntityInterface, GeoPointInterface, MembershipInterface, ZoneableEntityInterface, EntityMediaInterface, EquatableInterface, UuidEntityInterface, MailchimpCleanableContactInterface, PasswordAuthenticatedUserInterface, EntityAdministratorBlameableInterface, TranslatedTagInterface, EntityPostAddressInterface, ImageManageableInterface, ImageExposeInterface
{
    use EntityIdentityTrait;
    use EntityPersonNameTrait;
    use EntityPostAddressTrait;
    use EntityZoneTrait;
    use EntityUTMTrait;
    use EntityAdministratorBlameableTrait;
    use ImageTrait;
    use PublicIdTrait;

    public const PENDING = 'PENDING';
    public const ENABLED = 'ENABLED';
    public const TO_DELETE = 'TO_DELETE';
    public const DISABLED = 'DISABLED';

    #[Assert\Length(min: 3, max: 25, groups: ['Default', 'anonymize'])]
    #[Assert\Regex(pattern: '/^[a-z0-9 _-]+$/i', message: 'adherent.nickname.invalid_syntax', groups: ['anonymize'])]
    #[Assert\Regex(pattern: '/^[a-zÀ-ÿ0-9 .!_-]+$/i', message: 'adherent.nickname.invalid_extended_syntax')]
    #[Groups(['user_profile'])]
    #[ORM\Column(length: 25, unique: true, nullable: true)]
    private $nickname;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $nicknameUsed = false;

    #[ORM\Column(nullable: true)]
    private $password;

    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.gender.invalid_choice', groups: ['adhesion_complete_profile'])]
    #[Assert\NotBlank(message: 'common.gender.not_blank', groups: ['adhesion_complete_profile'])]
    #[Groups(['api_candidacy_read', 'profile_read', 'phoning_campaign_call_read', 'phoning_campaign_history_read_list', 'pap_campaign_history_read_list', 'pap_campaign_replies_list', 'phoning_campaign_replies_list', 'survey_replies_list', 'committee_candidacy:read', 'committee_election:read', 'national_event_inscription:webhook', 'profile_update', 'referral_read'])]
    #[ORM\Column(length: 6, nullable: true)]
    private $gender;

    #[Groups(['profile_read'])]
    #[ORM\Column(length: 80, nullable: true)]
    private $customGender;

    #[Groups(['user_profile', 'profile_read', 'elected_representative_read', 'adherent_autocomplete', 'my_team_read_list', 'profile_update', 'referral_read_with_referrer', 'agora_membership_read'])]
    #[ORM\Column(unique: true)]
    private $emailAddress;

    #[AssertPhoneNumber(message: 'common.phone_number.invalid', groups: ['additional_info', 'adhesion:further_information'])]
    #[Assert\Expression('not this.hasSmsSubscriptionType() or this.getPhone()', message: "Vous avez accepté de recevoir des informations du parti par SMS ou téléphone, cependant, vous n'avez pas précisé votre numéro de téléphone.", groups: ['adhesion:further_information'])]
    #[Groups(['profile_read', 'phoning_campaign_call_read', 'elected_representative_read', 'national_event_inscription:webhook', 'profile_update'])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    private $phone;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $phoneVerifiedAt = null;

    #[Assert\NotBlank(message: 'adherent.birthdate.not_blank', groups: ['additional_info', 'adhesion:further_information'])]
    #[Assert\Range(maxMessage: 'adherent.birthdate.minimum_required_age', max: '-15 years', groups: ['additional_info', 'adhesion:further_information'])]
    #[Groups(['profile_read', 'national_event_inscription:webhook', 'profile_update'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private $birthdate;

    #[Groups(['profile_read', 'profile_update'])]
    #[ORM\Column(nullable: true)]
    private $position;

    #[Groups(['profile_update'])]
    #[ORM\Column(length: 10, options: ['default' => self::PENDING])]
    private string $status = self::PENDING;

    #[Groups(['adherent_autocomplete', 'national_event_inscription:webhook'])]
    #[ORM\Column(type: 'datetime')]
    private $registeredAt;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $activatedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $membershipRemindedAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updatedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $lastLoggedAt;

    #[ORM\Column(nullable: true)]
    private ?string $lastLoginGroup = null;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $interests = [];

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $resubscribeEmailSentAt = null;

    /**
     * @var SubscriptionType[]|Collection
     */
    #[Groups(['profile_read', 'profile_update'])]
    #[ORM\ManyToMany(targetEntity: SubscriptionType::class, cascade: ['persist'])]
    private $subscriptionTypes;

    /**
     * @var AdherentZoneBasedRole[]|Collection
     */
    #[AssertZoneBasedRoles]
    #[Groups(['profile_update'])]
    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: AdherentZoneBasedRole::class, cascade: ['persist'], fetch: 'EAGER', orphanRemoval: true)]
    private Collection $zoneBasedRoles;

    /**
     * @var JecouteManagedArea|null
     */
    #[ORM\OneToOne(targetEntity: JecouteManagedArea::class, cascade: ['all'], orphanRemoval: true)]
    private $jecouteManagedArea;

    #[Groups(['profile_update'])]
    #[ORM\OneToOne(mappedBy: 'adherent', targetEntity: CommitteeMembership::class, cascade: ['all'])]
    private ?CommitteeMembership $committeeMembership = null;

    /**
     * @var Committee[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'animator', targetEntity: Committee::class, fetch: 'EXTRA_LAZY')]
    private $animatorCommittees;

    /**
     * @var Agora[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'president', targetEntity: Agora::class, fetch: 'EXTRA_LAZY')]
    public Collection $presidentOfAgoras;

    /**
     * @var Agora[]|Collection
     */
    #[ORM\ManyToMany(mappedBy: 'generalSecretaries', targetEntity: Agora::class, fetch: 'EXTRA_LAZY')]
    public Collection $generalSecretaryOfAgoras;

    /**
     * @var AgoraMembership[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: AgoraMembership::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $agoraMemberships;

    /**
     * @var InMemoryOAuthUser|null
     */
    private $oAuthUser;

    /**
     * @var string[]
     */
    private $roles = [];

    #[Groups(['adherent_elect_read', 'profile_update'])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private array $mandates = [];

    /**
     * @var Media|null
     */
    #[ORM\JoinColumn(name: 'media_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Media::class, cascade: ['persist'])]
    private $media;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private $displayMedia = true;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255, groups: ['Admin'])]
    #[Assert\Regex(pattern: '#^https?\:\/\/(?:www\.)?facebook.com\/#', message: 'legislative_candidate.facebook_page_url.invalid', groups: ['Admin'])]
    #[Assert\Url(groups: ['Admin'])]
    #[Groups(['profile_read', 'profile_update'])]
    #[ORM\Column(nullable: true)]
    private $facebookPageUrl;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255, groups: ['Admin'])]
    #[Assert\Regex(pattern: '#^https?\:\/\/(?:www\.)?twitter.com\/#', message: 'legislative_candidate.twitter_page_url.invalid', groups: ['Admin'])]
    #[Assert\Url(groups: ['Admin'])]
    #[Groups(['profile_read', 'profile_update'])]
    #[ORM\Column(nullable: true)]
    private $twitterPageUrl;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255, groups: ['Admin'])]
    #[Assert\Regex(pattern: '#^https?\:\/\/(?:www\.)?linkedin.com\/#', message: 'legislative_candidate.linkedin_page_url.invalid', groups: ['Admin'])]
    #[Assert\Url(groups: ['Admin'])]
    #[Groups(['profile_read', 'profile_update'])]
    #[ORM\Column(nullable: true)]
    private $linkedinPageUrl;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255, groups: ['Admin'])]
    #[Assert\Url(groups: ['Admin'])]
    #[Groups(['profile_read', 'profile_update'])]
    #[ORM\Column(nullable: true)]
    private $telegramPageUrl;

    /**
     * @var string|null
     */
    #[Groups(['profile_read'])]
    #[ORM\Column(nullable: true)]
    private $job;

    /**
     * @var string|null
     */
    #[Groups(['profile_read'])]
    #[ORM\Column(nullable: true)]
    private $activityArea;

    /**
     * @var string|null
     */
    #[Assert\Country(message: 'common.nationality.invalid', groups: ['adhesion_complete_profile'])]
    #[Assert\NotBlank(groups: ['adhesion_complete_profile'])]
    #[Groups(['profile_read', 'profile_update'])]
    #[ORM\Column(length: 2, nullable: true)]
    private $nationality;

    #[Groups(['user_profile'])]
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    public bool $canaryTester = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $sandboxMode = false;

    /**
     * Mailchimp unsubscribed status
     *
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $emailUnsubscribed = false;

    /**
     * Mailchimp unsubscribed date
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $emailUnsubscribedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $unsubscribeRequestedAt = null;

    /**
     * @var CandidateManagedArea|null
     */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: CandidateManagedArea::class, cascade: ['all'], orphanRemoval: true)]
    private $candidateManagedArea;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $nationalRole = false;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $nationalCommunicationRole = false;

    /**
     * @var Collection|AdherentCharterInterface[]
     */
    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: AdherentCharter\AbstractAdherentCharter::class, cascade: ['all'])]
    private $charters;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $electionResultsReporter = false;

    /**
     * @var \DateTime|null
     */
    #[Groups(['certification_request_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $certifiedAt;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $source;

    /**
     * @var CertificationRequest[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: CertificationRequest::class, cascade: ['all'], fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private $certificationRequests;

    /**
     * @var DelegatedAccess[]|ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'delegated', targetEntity: DelegatedAccess::class, cascade: ['all'])]
    private $receivedDelegatedAccesses;

    /**
     * @var AdherentMandateInterface[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: AbstractAdherentMandate::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private $adherentMandates;

    /**
     * @var ProvisionalSupervisor[]
     */
    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: ProvisionalSupervisor::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private $provisionalSupervisors;

    /**
     * @var Member[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: Member::class, fetch: 'EXTRA_LAZY')]
    private $teamMemberships;

    /**
     * @var PostAddress
     */
    #[Groups(['profile_read', 'profile_update'])]
    #[ORM\Embedded(class: PostAddress::class, columnPrefix: 'address_')]
    protected $postAddress;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $voteInspector = false;

    #[ORM\Column(nullable: true)]
    private ?string $mailchimpStatus = ContactStatusEnum::SUBSCRIBED;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $phoningManagerRole = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $papNationalManagerRole = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $papUserRole = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $meetingScanner = false;

    #[Groups(['profile_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastMembershipDonation = null;

    #[Groups(['profile_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $firstMembershipDonation = null;

    #[Groups(['profile_read'])]
    #[ORM\Column(options: ['default' => MembershipTypeEnum::EXCLUSIVE])]
    public string $partyMembership = MembershipTypeEnum::EXCLUSIVE;

    private ?string $authAppCode = null;
    private ?string $authAppVersion = null;
    public ?AppSession $currentAppSession = null;

    #[ORM\Column(nullable: true)]
    public ?string $emailStatusComment = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $lastMailchimpFailedSyncResponse = null;

    /**
     * @var Registration[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: Registration::class, cascade: ['all'], fetch: 'EXTRA_LAZY')]
    private Collection $campusRegistrations;

    #[Groups(['adherent_elect_read'])]
    #[ORM\Column(nullable: true)]
    private ?string $contributionStatus = null;

    #[Assert\Expression('!value || (!this.findActifNationalMandates() and this.findActifLocalMandates())', message: 'adherent.elect.exempt_invalid_status', groups: ['adherent_elect_update'])]
    #[Groups(['adherent_elect_read', 'adherent_elect_update'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $exemptFromCotisation = false;

    #[Groups(['adherent_elect_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $contributedAt = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(targetEntity: Contribution::class)]
    private ?Contribution $lastContribution = null;

    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: Contribution::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $contributions;

    #[Groups(['adherent_elect_read'])]
    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: Payment::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\OrderBy(['date' => 'DESC'])]
    private Collection $payments;

    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: RevenueDeclaration::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $revenueDeclarations;

    #[Groups(['national_event_inscription:webhook', 'jemarche_user_profile'])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    public array $tags = [];

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $v2 = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $forcedMembership = false;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private array $finishedAdhesionSteps = [];

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $acceptMemberCard = true;

    #[Groups(['profile_update'])]
    #[ORM\ManyToMany(targetEntity: AdherentStaticLabel::class, fetch: 'EXTRA_LAZY')]
    private Collection $staticLabels;

    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: AppSession::class, fetch: 'EXTRA_LAZY')]
    private Collection $appSessions;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $resubscribeEmailStartedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $resubscribeResponse = null;

    public function __construct()
    {
        $this->animatorCommittees = new ArrayCollection();
        $this->presidentOfAgoras = new ArrayCollection();
        $this->generalSecretaryOfAgoras = new ArrayCollection();
        $this->agoraMemberships = new ArrayCollection();
        $this->subscriptionTypes = new ArrayCollection();
        $this->zones = new ZoneCollection();
        $this->charters = new AdherentCharterCollection();
        $this->certificationRequests = new ArrayCollection();
        $this->receivedDelegatedAccesses = new ArrayCollection();
        $this->adherentMandates = new ArrayCollection();
        $this->provisionalSupervisors = new ArrayCollection();
        $this->teamMemberships = new ArrayCollection();
        $this->zoneBasedRoles = new ArrayCollection();
        $this->campusRegistrations = new ArrayCollection();
        $this->contributions = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->revenueDeclarations = new ArrayCollection();
        $this->staticLabels = new ArrayCollection();
        $this->appSessions = new ArrayCollection();
    }

    public static function createBlank(
        string $publicId,
        string $gender,
        string $firstName,
        string $lastName,
        string $nationality,
        PostAddress $postAddress,
        string $email,
        ?PhoneNumber $phone,
        ?\DateTimeInterface $birthdate,
        ?\DateTime $registeredAt = null,
        ?string $partyMembership = null,
    ): self {
        $adherent = new self();

        $adherent->uuid = Uuid::uuid4();
        $adherent->publicId = $publicId;
        $adherent->gender = $gender;
        $adherent->firstName = $firstName;
        $adherent->lastName = $lastName;
        $adherent->nationality = $nationality;
        $adherent->postAddress = $postAddress;
        $adherent->emailAddress = $email;
        $adherent->phone = $phone;
        $adherent->birthdate = $birthdate;
        $adherent->partyMembership = $partyMembership ?? MembershipTypeEnum::EXCLUSIVE;
        $adherent->registeredAt = $registeredAt ?? new \DateTime('now');

        $adherent->password = Uuid::uuid4();

        return $adherent;
    }

    public static function create(
        UuidInterface $uuid,
        string $publicId,
        string $emailAddress,
        ?string $password,
        ?string $gender,
        string $firstName,
        string $lastName,
        ?\DateTimeInterface $birthDate = null,
        ?string $position = null,
        ?PostAddress $postAddress = null,
        ?PhoneNumber $phone = null,
        ?string $nickname = null,
        bool $nicknameUsed = false,
        string $status = self::PENDING,
        string $registeredAt = 'now',
        ?array $mandates = [],
        ?string $nationality = null,
        ?string $customGender = null,
        ?array $finishedSteps = null,
    ): self {
        $adherent = new self();

        $adherent->uuid = $uuid;
        $adherent->publicId = $publicId;
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
        $adherent->registeredAt = new \DateTime($registeredAt);
        $adherent->mandates = $mandates ?? [];
        $adherent->nationality = $nationality;
        $adherent->customGender = $customGender;
        $adherent->finishedAdhesionSteps = $finishedSteps ?? [];

        return $adherent;
    }

    public function getIdentifier()
    {
        return $this->getUuidAsString();
    }

    public static function createUuid(string $email): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, mb_strtolower($email));
    }

    public function getUuidAsString(): string
    {
        return $this->getUuid()->toString();
    }

    public function getSubscriptionExternalIds(): array
    {
        return array_values(array_filter(array_map(function (SubscriptionType $subscription) {
            return $subscription->getExternalId();
        }, $this->getSubscriptionTypes())));
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];

        if ($this->isDeputy()) {
            $roles[] = 'ROLE_DEPUTY';
        }

        if ($this->isSenator()) {
            $roles[] = 'ROLE_SENATOR';
        }

        if ($this->isPresidentDepartmentalAssembly()) {
            $roles[] = 'ROLE_PRESIDENT_DEPARTMENTAL_ASSEMBLY';
        }

        if ($this->isFdeCoordinator()) {
            $roles[] = 'ROLE_FDE_COORDINATOR';
        }

        if ($this->isRegionalCoordinator()) {
            $roles[] = 'ROLE_REGIONAL_COORDINATOR';
        }

        if ($this->isRegionalDelegate()) {
            $roles[] = 'ROLE_REGIONAL_DELEGATE';
        }

        if ($this->isHost()) {
            $roles[] = 'ROLE_HOST';
        }

        if ($this->isSupervisor()) {
            $roles[] = 'ROLE_SUPERVISOR';
        }

        if ($this->isAnimator()) {
            $roles[] = 'ROLE_ANIMATOR';
        }

        if ($this->isProcurationsManager()) {
            $roles[] = 'ROLE_PROCURATION_MANAGER';
        }

        if ($this->isJecouteManager()) {
            $roles[] = 'ROLE_JECOUTE_MANAGER';
        }

        if ($this->isLegislativeCandidate()) {
            $roles[] = 'ROLE_LEGISLATIVE_CANDIDATE';
        }

        if ($this->canaryTester) {
            $roles[] = 'ROLE_CANARY_TESTER';
        }

        if ($this->hasNationalRole()) {
            $roles[] = 'ROLE_NATIONAL';
        }

        if ($this->isElectionResultsReporter()) {
            $roles[] = 'ROLE_ELECTION_RESULTS_REPORTER';
        }

        foreach ($this->receivedDelegatedAccesses as $delegatedAccess) {
            $roles[] = 'ROLE_DELEGATED_'.strtoupper($delegatedAccess->getType());
        }

        foreach ($this->zoneBasedRoles as $zoneBasedRole) {
            $roles[] = 'ROLE_DELEGATED_'.strtoupper($zoneBasedRole->getType());
        }

        if ($this->isHeadedRegionalCandidate()) {
            $roles[] = 'ROLE_CANDIDATE_REGIONAL_HEADED';
        }

        if ($this->isLeaderRegionalCandidate()) {
            $roles[] = 'ROLE_CANDIDATE_REGIONAL_LEADER';
        }

        if ($this->isDepartmentalCandidate()) {
            $roles[] = 'ROLE_CANDIDATE_DEPARTMENTAL';
        }

        if ($this->isPhoningCampaignTeamMember()) {
            $roles[] = 'ROLE_PHONING_CAMPAIGN_MEMBER';
        }

        if ($this->voteInspector) {
            $roles[] = 'ROLE_VOTE_INSPECTOR';
        }

        if ($this->hasPapUserRole()) {
            $roles[] = 'ROLE_PAP_USER';
        }

        if ($this->meetingScanner) {
            $roles[] = 'ROLE_MEETING_SCANNER';
        }

        if ($this->isCorrespondent()) {
            $roles[] = 'ROLE_CORRESPONDENT';
        }

        // Must be at the end as it uses $roles array
        if ($this->isAdherentMessageRedactor($roles)) {
            $roles[] = 'ROLE_MESSAGE_REDACTOR';
        }

        return array_merge(array_unique($roles), $this->roles);
    }

    public function addRoles(array $roles): void
    {
        foreach ($roles as $role) {
            $this->roles[] = $role;
        }
    }

    public function getType(): string
    {
        if ($this->isSupervisor() || $this->isHost()) {
            return 'HOST';
        }

        return 'ADHERENT';
    }

    public function hasAdvancedPrivileges(): bool
    {
        return $this->isRegionalCoordinator()
            || $this->isProcurationsManager()
            || $this->isJecouteManager()
            || $this->isSupervisor()
            || $this->isHost()
            || $this->isDeputy()
            || $this->isDelegatedDeputy()
            || $this->isElectionResultsReporter()
            || $this->isHeadedRegionalCandidate()
            || $this->isLeaderRegionalCandidate()
            || $this->isDepartmentalCandidate()
            || $this->isDelegatedCandidate()
            || $this->isLegislativeCandidate()
            || $this->isCorrespondent()
            || $this->isPresidentDepartmentalAssembly()
            || $this->isDelegatedPresidentDepartmentalAssembly()
            || $this->isAnimator()
            || $this->isDelegatedAnimator();
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getUserIdentifier(): string
    {
        return $this->emailAddress;
    }

    public function eraseCredentials(): void
    {
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getGenderName(): ?string
    {
        return array_search($this->gender, Genders::CHOICES);
    }

    public function getCivility(): ?CivilityEnum
    {
        return CivilityEnum::fromGender($this->gender);
    }

    public function getCustomGender(): ?string
    {
        return $this->customGender;
    }

    public function isForeignResident(): bool
    {
        return AddressInterface::FRANCE !== strtoupper($this->getCountry());
    }

    public function isParisResident(): bool
    {
        return !$this->isForeignResident() && AreaUtils::PREFIX_POSTALCODE_PARIS_DISTRICTS === substr($this->getPostalCode(), 0, 2);
    }

    public function isFemale(): bool
    {
        return Genders::FEMALE === $this->gender;
    }

    public function isOtherGender(): bool
    {
        return Genders::OTHER === $this->gender;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTime $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    #[Groups(['export', 'phoning_campaign_history_read_list', 'pap_campaign_history_read_list', 'pap_campaign_replies_list', 'phoning_campaign_replies_list', 'survey_replies_list'])]
    public function getAge(\DateTime $from = new \DateTime()): ?int
    {
        return $this->birthdate?->diff($from)->y;
    }

    public function isMinor(?\DateTime $date = null): bool
    {
        return null === $this->birthdate || $this->birthdate->diff($date ?? new \DateTime())->y < 18;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): void
    {
        if (!ActivityPositionsEnum::exists($position)) {
            throw new \InvalidArgumentException(\sprintf('Invalid position "%s", known positions are "%s".', $position, implode('", "', ActivityPositionsEnum::ALL)));
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

    public function isPending(): bool
    {
        return self::PENDING === $this->status;
    }

    public function isDisabled(): bool
    {
        return self::DISABLED === $this->status;
    }

    public function isEnabled(): bool
    {
        return self::ENABLED === $this->status;
    }

    public function getActivatedAt(): ?\DateTime
    {
        return $this->activatedAt;
    }

    public function setMembershipReminded(): void
    {
        $this->membershipRemindedAt = new \DateTime();
    }

    public function changePassword(string $newPassword): void
    {
        $this->password = $newPassword;
    }

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

    #[Groups(['profile_read'])]
    public function getMainZone(): ?Zone
    {
        return $this->getAssemblyZone();
    }

    public function hasSmsSubscriptionType(): bool
    {
        return $this->hasSubscriptionType(SubscriptionTypeEnum::MILITANT_ACTION_SMS);
    }

    public function findAppSessions(Client $client, bool $activeOnly): array
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('client', $client))
            ->orderBy([
                'unsubscribedAt' => Order::Ascending,
                'lastActivityDate' => Order::Descending,
            ])
        ;

        if ($activeOnly) {
            $criteria->andWhere($criteria::expr()->eq('status', SessionStatusEnum::ACTIVE));
        }

        return $this->appSessions->matching($criteria)->toArray();
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
        $this->enable($timestamp);
    }

    public function enable(string $timestamp = 'now'): void
    {
        if (self::PENDING !== $this->status) {
            throw new AdherentAlreadyEnabledException($this->uuid);
        }

        $this->status = self::ENABLED;
        $this->activatedAt ??= new \DateTime($timestamp);
        $this->finishAdhesionStep(AdhesionStepEnum::ACTIVATION);
    }

    /**
     * Resets the Adherent password using a reset password token.
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

        $this->password = $newPassword;
        $this->finishAdhesionStep(AdhesionStepEnum::PASSWORD);
    }

    public function changeEmail(AdherentChangeEmailToken $token): void
    {
        if (!$token->getEmail()) {
            throw new \InvalidArgumentException('Token must have a new email.');
        }

        $token->consume($this);

        $this->emailAddress = $token->getEmail();
    }

    /**
     * Records the adherent last login date and time.
     *
     * @param string|int $timestamp a valid date representation as a string or integer
     */
    public function recordLastLoginTime($timestamp = 'now'): void
    {
        $this->lastLoggedAt = new \DateTime($timestamp);

        $this->setLastLoginGroup(LastLoginGroupEnum::LESS_THAN_1_MONTH);
    }

    /**
     * Returns the last login date and time of this adherent.
     */
    public function getLastLoggedAt(): ?\DateTime
    {
        return $this->lastLoggedAt;
    }

    public function getLastLoginGroup(): ?string
    {
        return $this->lastLoginGroup;
    }

    public function setLastLoginGroup(?string $lastLoginGroup): void
    {
        $this->lastLoginGroup = $lastLoginGroup;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }

    public function setInterests(array $interests): void
    {
        $this->interests = $interests;
    }

    public function updateProfile(AdherentProfile $adherentProfile, PostAddress $postAddress): void
    {
        $this->customGender = $adherentProfile->getCustomGender();
        $this->gender = $adherentProfile->getGender();
        $this->firstName = $adherentProfile->getFirstName();
        $this->lastName = $adherentProfile->getLastName();
        $this->birthdate = $adherentProfile->getBirthdate();
        $this->position = $adherentProfile->getPosition();
        $this->phone = $adherentProfile->getPhone();
        $this->nationality = $adherentProfile->getNationality();
        $this->facebookPageUrl = $adherentProfile->getFacebookPageUrl();
        $this->twitterPageUrl = $adherentProfile->getTwitterPageUrl();
        $this->telegramPageUrl = $adherentProfile->getTelegramPageUrl();
        $this->linkedinPageUrl = $adherentProfile->getLinkedinPageUrl();
        $this->job = $adherentProfile->getJob();
        $this->activityArea = $adherentProfile->getActivityArea();
        $this->mandates = $adherentProfile->getMandates();
        $this->interests = $adherentProfile->getInterests();
        $this->partyMembership = $adherentProfile->partyMembership ?? $this->partyMembership;

        if (!$this->postAddress->equals($postAddress)) {
            $this->postAddress = $postAddress;
        }
    }

    public function updateMembershipFormAdminAdherentCreateCommand(
        AdherentCreateCommand $command,
        Administrator $administrator,
    ): void {
        if (!$this->isCertified()) {
            $this->gender = $command->gender;
            $this->firstName = $command->firstName;
            $this->lastName = $command->lastName;
            $this->birthdate = $command->birthdate;
            $this->nationality = $command->nationality;
        }

        $this->postAddress = PostAddressFactory::createFromAddress($command->address);
        $this->phone = $command->phone;
        $this->partyMembership = $command->partyMembership;
        $this->updatedByAdministrator = $administrator;

        if (!$this->isRenaissanceUser()) {
            $this->source = $command->getSource();
        }
    }

    /**
     * Joins a committee as a HOST privileged person.
     */
    public function hostCommittee(
        Committee $committee,
        ?\DateTimeInterface $subscriptionDate = null,
    ): CommitteeMembership {
        return $this->joinCommittee($committee, CommitteeMembership::COMMITTEE_HOST, $subscriptionDate ?? new \DateTime());
    }

    /**
     * Joins a committee as a simple FOLLOWER privileged person.
     */
    public function followCommittee(
        Committee $committee,
        ?\DateTimeInterface $subscriptionDate = null,
        ?CommitteeMembershipTriggerEnum $trigger = null,
    ): CommitteeMembership {
        return $this->joinCommittee(
            $committee,
            CommitteeMembership::COMMITTEE_FOLLOWER,
            $subscriptionDate ?? new \DateTime(),
            $trigger
        );
    }

    private function joinCommittee(
        Committee $committee,
        string $privilege,
        \DateTimeInterface $subscriptionDate,
        ?CommitteeMembershipTriggerEnum $trigger = null,
    ): CommitteeMembership {
        return $this->committeeMembership = CommitteeMembership::createForAdherent($committee, $this, $privilege, $subscriptionDate, $trigger);
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

    #[Groups(['export'])]
    public function getRegisteredAt(): ?\DateTime
    {
        return $this->registeredAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /** @return AdherentZoneBasedRole[] */
    public function getZoneBasedRoles(): array
    {
        return $this->zoneBasedRoles->toArray();
    }

    public function addZoneBasedRole(AdherentZoneBasedRole $role): void
    {
        if (!$this->zoneBasedRoles->contains($role)) {
            $role->setAdherent($this);
            $this->zoneBasedRoles->add($role);
        }
    }

    public function removeZoneBasedRole(AdherentZoneBasedRole $role): void
    {
        $this->zoneBasedRoles->removeElement($role);
    }

    public function revokeJecouteManager(): void
    {
        $this->jecouteManagedArea = null;
    }

    public function isProcurationsManager(): bool
    {
        return $this->hasZoneBasedRole(ScopeEnum::PROCURATIONS_MANAGER);
    }

    public function getJecouteManagedArea(): ?JecouteManagedArea
    {
        return $this->jecouteManagedArea;
    }

    public function setJecouteManagedZone(?Zone $zone = null): void
    {
        if (!$this->jecouteManagedArea) {
            $this->jecouteManagedArea = new JecouteManagedArea();
        }

        $this->jecouteManagedArea->setZone($zone);
    }

    public function isJecouteManager(): bool
    {
        return $this->jecouteManagedArea instanceof JecouteManagedArea && $this->jecouteManagedArea->getZone();
    }

    #[Groups(['profile_read'])]
    public function getCommitteeMembership(): ?CommitteeMembership
    {
        return $this->committeeMembership;
    }

    public function setCommitteeMembership(?CommitteeMembership $committeeMembership): void
    {
        $this->committeeMembership = $committeeMembership;
    }

    public function hasVotingCommitteeMembership(): bool
    {
        return (bool) $this->committeeMembership?->isVotingCommittee();
    }

    public function getMembershipFor(Committee $committee): ?CommitteeMembership
    {
        return $this->committeeMembership?->matches($this, $committee) ? $this->committeeMembership : null;
    }

    public function isHost(): bool
    {
        return (bool) $this->committeeMembership?->isHostMember();
    }

    public function isHostOf(Committee $committee): bool
    {
        if (!$membership = $this->getMembershipFor($committee)) {
            return false;
        }

        return $membership->isHostMember();
    }

    public function isSupervisor(?bool $isProvisional = null): bool
    {
        return $this->getSupervisorMandates($isProvisional)->count() > 0;
    }

    public function isAnimator(): bool
    {
        return !$this->animatorCommittees->isEmpty();
    }

    /**
     * @return Committee[]
     */
    public function getAnimatorCommittees(): array
    {
        return $this->animatorCommittees->toArray();
    }

    public function isSupervisorOf(Committee $committee, ?bool $isProvisional = null): bool
    {
        return $this->adherentMandates->filter(static function (AdherentMandateInterface $mandate) use ($committee, $isProvisional) {
            return $mandate instanceof CommitteeAdherentMandate
                && $mandate->getCommittee() === $committee
                && null === $mandate->getFinishAt()
                && CommitteeMandateQualityEnum::SUPERVISOR === $mandate->getQuality()
                && (null === $isProvisional || $mandate->isProvisional() === $isProvisional);
        })->count() > 0;
    }

    public function isNicknameUsed(): bool
    {
        return $this->nicknameUsed;
    }

    #[Groups(['user_profile'])]
    public function getUseNickname(): bool
    {
        return $this->isNicknameUsed();
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

    public function setSubscriptionTypes(array $subscriptionTypes): void
    {
        $codes = array_map(static function (SubscriptionType $type) {
            return $type->getCode();
        }, $subscriptionTypes);

        foreach ($this->subscriptionTypes as $type) {
            if (!\in_array($type->getCode(), $codes, true)) {
                $this->removeSubscriptionType($type);
            }
        }

        foreach ($subscriptionTypes as $type) {
            $this->addSubscriptionType($type);
        }
    }

    public function isDelegatedDeputy(): bool
    {
        return \count($this->getReceivedDelegatedAccessOfType('deputy')) > 0;
    }

    public function isDelegatedAnimator(): bool
    {
        return \count($this->getReceivedDelegatedAccessOfType(ScopeEnum::ANIMATOR)) > 0;
    }

    public function isDelegatedPresidentDepartmentalAssembly(): bool
    {
        return \count($this->getReceivedDelegatedAccessOfType(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY)) > 0;
    }

    public function getOAuthUser(): InMemoryOAuthUser
    {
        if (!$this->oAuthUser) {
            $this->oAuthUser = new InMemoryOAuthUser($this->uuid);
        }

        return $this->oAuthUser;
    }

    public function __serialize(): array
    {
        return [
            $this->id,
            $this->emailAddress,
            $this->password,
            $this->getRoles(),
        ];
    }

    public function __unserialize(array $serialized): void
    {
        [$this->id, $this->emailAddress, $this->password, $this->roles] = $serialized;
    }

    public function getMandates(): ?array
    {
        return $this->mandates;
    }

    public function setMandates(?array $mandates): void
    {
        $this->mandates = $mandates;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media = null): void
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

    public function setNationality(?string $nationality)
    {
        $this->nationality = $nationality;
    }

    #[Groups(['export'])]
    public function getCityName(): ?string
    {
        return $this->postAddress->getCityName();
    }

    public function setEmailUnsubscribed(bool $value): void
    {
        if ($value) {
            $this->emailUnsubscribedAt = new \DateTime();
        }

        $this->emailUnsubscribed = $value;

        if ($value) {
            $this->mailchimpStatus = ContactStatusEnum::UNSUBSCRIBED;
        } else {
            $this->mailchimpStatus = ContactStatusEnum::SUBSCRIBED;
            $this->emailStatusComment = null;
        }
    }

    public function markAsUnsubscribe(): void
    {
        $this->setEmailUnsubscribed(true);
        $this->unsubscribeRequestedAt = new \DateTime();
    }

    private function isAdherentMessageRedactor(array $roles): bool
    {
        return
            array_intersect($roles, [
                'ROLE_DEPUTY',
                'ROLE_HOST',
                'ROLE_SUPERVISOR',
                'ROLE_ANIMATOR',
                'ROLE_LEGISLATIVE_CANDIDATE',
                'ROLE_PRESIDENT_DEPARTMENTAL_ASSEMBLY',
            ])
            || $this->isCandidate()
            || $this->isDelegatedCandidate()
            || $this->isCorrespondent()
            || $this->isRegionalCoordinator()
            || $this->isRegionalDelegate()
            || $this->isFdeCoordinator()
            || $this->hasDelegatedAccess(DelegatedAccess::ACCESS_MESSAGES)
            || $this->hasDelegatedScopeFeature(FeatureEnum::MESSAGES)
            || $this->hasDelegatedScopeFeature(FeatureEnum::PUBLICATIONS);
    }

    public function __clone()
    {
        $this->subscriptionTypes = new ArrayCollection($this->subscriptionTypes->toArray());
        $this->postAddress = clone $this->postAddress;
    }

    public function hasNationalRole(): bool
    {
        return $this->nationalRole;
    }

    public function setNationalRole(bool $nationalRole): void
    {
        $this->nationalRole = $nationalRole;
    }

    public function hasNationalCommunicationRole(): bool
    {
        return $this->nationalCommunicationRole;
    }

    public function setNationalCommunicationRole(bool $nationalCommunicationRole): void
    {
        $this->nationalCommunicationRole = $nationalCommunicationRole;
    }

    public function isPhoningCampaignTeamMember(): bool
    {
        return !$this->teamMemberships->isEmpty();
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

    /**
     * @param UserInterface|self $user
     */
    public function isEqualTo(UserInterface $user): bool
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

    public function getCertifiedAt(): ?\DateTime
    {
        return $this->certifiedAt;
    }

    #[Groups(['user_profile', 'profile_read'])]
    public function isCertified(): bool
    {
        return null !== $this->certifiedAt;
    }

    public function certify(): void
    {
        if ($this->certifiedAt) {
            return;
        }

        $this->certifiedAt = new \DateTime();
    }

    public function uncertify(): void
    {
        $this->certifiedAt = null;
    }

    public function getCertificationRequests(): CertificationRequestCollection
    {
        if (!$this->certificationRequests instanceof CertificationRequestCollection) {
            $this->certificationRequests = new CertificationRequestCollection($this->certificationRequests->toArray());
        }

        return $this->certificationRequests;
    }

    #[Groups(['certification_request_read'])]
    public function getLastCertificationRequest(): ?CertificationRequest
    {
        return $this->certificationRequests->last() ?: null;
    }

    public function startCertificationRequest(): CertificationRequest
    {
        if ($this->getCertificationRequests()->hasPendingCertificationRequest()) {
            throw new \LogicException('Adherent already has a pending certification request.');
        }

        $pendingCertificationRequest = new CertificationRequest($this);

        $this->certificationRequests->add($pendingCertificationRequest);

        return $pendingCertificationRequest;
    }

    /**
     * @return DelegatedAccess[]|Collection|iterable
     */
    public function getReceivedDelegatedAccesses(): iterable
    {
        return $this->receivedDelegatedAccesses;
    }

    public function getReceivedDelegatedAccessByUuid(?string $delegatedAccessUuid): ?DelegatedAccess
    {
        if (null === $delegatedAccessUuid) {
            return null;
        }

        /** @var DelegatedAccess $delegatedAccess */
        foreach ($this->receivedDelegatedAccesses as $delegatedAccess) {
            if ($delegatedAccess->getUuid()->toString() === $delegatedAccessUuid) {
                return $delegatedAccess;
            }
        }

        return null;
    }

    public function addReceivedDelegatedAccess(DelegatedAccess $delegatedAccess): void
    {
        if (!$this->receivedDelegatedAccesses->contains($delegatedAccess)) {
            $this->receivedDelegatedAccesses->add($delegatedAccess);
        }
    }

    public function removeReceivedDelegatedAccess(DelegatedAccess $delegatedAccess): void
    {
        $this->receivedDelegatedAccesses->removeElement($delegatedAccess);
    }

    public function setReceivedDelegatedAccesses(iterable $delegatedAccesses): void
    {
        $this->receivedDelegatedAccesses->clear();
        foreach ($delegatedAccesses as $delegatedAccess) {
            $this->addReceivedDelegatedAccess($delegatedAccess);
        }
    }

    /**
     * @return DelegatedAccess[]|Collection
     */
    public function getReceivedDelegatedAccessOfType(string $type): Collection
    {
        return $this->receivedDelegatedAccesses->filter(static function (DelegatedAccess $delegatedAccess) use ($type) {
            return $delegatedAccess->getType() === $type && (\count($delegatedAccess->getAccesses()) || \count($delegatedAccess->getScopeFeatures()));
        });
    }

    public function hasDelegatedFromUser(self $delegator, ?string $access = null): bool
    {
        /** @var DelegatedAccess $delegatedAccess */
        foreach ($this->getReceivedDelegatedAccesses() as $delegatedAccess) {
            if ($delegatedAccess->getDelegator() === $delegator && (!$access || \in_array($access, array_merge($delegatedAccess->getAccesses(), $delegatedAccess->getScopeFeatures()), true))) {
                return true;
            }
        }

        return false;
    }

    public function hasDelegatedAccess(string $access): bool
    {
        foreach ($this->receivedDelegatedAccesses as $delegatedAccess) {
            if (\in_array($access, $delegatedAccess->getAccesses())) {
                return true;
            }
        }

        return false;
    }

    public function hasDelegatedScopeFeature(string $feature): bool
    {
        foreach ($this->receivedDelegatedAccesses as $delegatedAccess) {
            if (\in_array($feature, $delegatedAccess->getScopeFeatures())) {
                return true;
            }
        }

        return false;
    }

    public function getCandidateManagedArea(): ?CandidateManagedArea
    {
        return $this->candidateManagedArea;
    }

    public function setCandidateManagedArea(?CandidateManagedArea $candidateManagedArea): void
    {
        $this->candidateManagedArea = $candidateManagedArea;
    }

    public function isHeadedRegionalCandidate(): bool
    {
        return $this->candidateManagedArea && $this->candidateManagedArea->isRegionalZone();
    }

    public function isLeaderRegionalCandidate(): bool
    {
        return $this->candidateManagedArea && $this->candidateManagedArea->isDepartmentalZone();
    }

    public function isDepartmentalCandidate(): bool
    {
        return $this->candidateManagedArea && $this->candidateManagedArea->isCantonalZone();
    }

    public function isCandidate(): bool
    {
        return $this->candidateManagedArea instanceof CandidateManagedArea;
    }

    public function isDelegatedCandidate(): bool
    {
        return \count($this->getReceivedDelegatedAccessOfType(DelegatedAccessEnum::TYPE_CANDIDATE)) > 0;
    }

    public function isDelegatedHeadedRegionalCandidate(): bool
    {
        foreach ($this->getReceivedDelegatedAccessOfType(DelegatedAccessEnum::TYPE_CANDIDATE) as $delegatedAccess) {
            if ($delegatedAccess->getDelegator()->isHeadedRegionalCandidate()) {
                return true;
            }
        }

        return false;
    }

    public function isDelegatedLeaderRegionalCandidate(): bool
    {
        foreach ($this->getReceivedDelegatedAccessOfType(DelegatedAccessEnum::TYPE_CANDIDATE) as $delegatedAccess) {
            if ($delegatedAccess->getDelegator()->isLeaderRegionalCandidate()) {
                return true;
            }
        }

        return false;
    }

    public function isDelegatedDepartmentalCandidate(): bool
    {
        foreach ($this->getReceivedDelegatedAccessOfType(DelegatedAccessEnum::TYPE_CANDIDATE) as $delegatedAccess) {
            if ($delegatedAccess->getDelegator()->isDepartmentalCandidate()) {
                return true;
            }
        }

        return false;
    }

    public function isCorrespondent(): bool
    {
        return $this->hasZoneBasedRole(ScopeEnum::CORRESPONDENT);
    }

    public function getCorrespondentZone(): Zone
    {
        return $this->findZoneBasedRole(ScopeEnum::CORRESPONDENT)->getZones()->first();
    }

    public function isLegislativeCandidate(): bool
    {
        return $this->hasZoneBasedRole(ScopeEnum::LEGISLATIVE_CANDIDATE);
    }

    public function getLegislativeCandidateZone(): Zone
    {
        return $this->findZoneBasedRole(ScopeEnum::LEGISLATIVE_CANDIDATE)->getZones()->first();
    }

    public function isDeputy(): bool
    {
        return $this->hasZoneBasedRole(ScopeEnum::DEPUTY);
    }

    public function isSenator(): bool
    {
        return $this->hasZoneBasedRole(ScopeEnum::SENATOR);
    }

    public function isPresidentDepartmentalAssembly(): bool
    {
        return $this->hasZoneBasedRole(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);
    }

    public function isMunicipalCandidate(): bool
    {
        return $this->hasZoneBasedRole(ScopeEnum::MUNICIPAL_CANDIDATE);
    }

    public function getDeputyZone(): ?Zone
    {
        return $this->isDeputy() ? $this->findZoneBasedRole(ScopeEnum::DEPUTY)->getZones()->first() : null;
    }

    public function isRegionalCoordinator(): bool
    {
        return $this->hasZoneBasedRole(ScopeEnum::REGIONAL_COORDINATOR);
    }

    public function isRegionalDelegate(): bool
    {
        return $this->hasZoneBasedRole(ScopeEnum::REGIONAL_DELEGATE);
    }

    public function isFdeCoordinator(): bool
    {
        return $this->hasZoneBasedRole(ScopeEnum::FDE_COORDINATOR);
    }

    /**
     * @return Zone[]
     */
    public function getRegionalCoordinatorZone(): array
    {
        return $this->isRegionalCoordinator() ? $this->findZoneBasedRole(ScopeEnum::REGIONAL_COORDINATOR)->getZones()->toArray() : [];
    }

    /**
     * @return Zone[]
     */
    public function getPresidentDepartmentalAssemblyZones(): array
    {
        return $this->isPresidentDepartmentalAssembly() ? $this->findZoneBasedRole(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY)->getZones()->toArray() : [];
    }

    public function getMunicipalCandidateZone(): ?Zone
    {
        return $this->isMunicipalCandidate() ? $this->findZoneBasedRole(ScopeEnum::MUNICIPAL_CANDIDATE)?->getZones()->toArray()[0] ?? null : null;
    }

    public function getFacebookPageUrl(): ?string
    {
        return $this->facebookPageUrl;
    }

    public function setFacebookPageUrl(?string $facebookPageUrl): void
    {
        $this->facebookPageUrl = $facebookPageUrl;
    }

    public function getTwitterPageUrl(): ?string
    {
        return $this->twitterPageUrl;
    }

    public function setTwitterPageUrl(?string $twitterPageUrl): void
    {
        $this->twitterPageUrl = $twitterPageUrl;
    }

    public function getLinkedinPageUrl(): ?string
    {
        return $this->linkedinPageUrl;
    }

    public function setLinkedinPageUrl(?string $linkedinPageUrl): void
    {
        $this->linkedinPageUrl = $linkedinPageUrl;
    }

    public function getTelegramPageUrl(): ?string
    {
        return $this->telegramPageUrl;
    }

    public function setTelegramPageUrl(?string $telegramPageUrl): void
    {
        $this->telegramPageUrl = $telegramPageUrl;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(?string $job): void
    {
        $this->job = $job;
    }

    public function getActivityArea(): ?string
    {
        return $this->activityArea;
    }

    public function setActivityArea(?string $activityArea): void
    {
        $this->activityArea = $activityArea;
    }

    public function isVoteInspector(): bool
    {
        return $this->voteInspector;
    }

    public function setVoteInspector(bool $voteInspector): void
    {
        $this->voteInspector = $voteInspector;
    }

    public function getAdherentMandates(): Collection
    {
        return $this->adherentMandates;
    }

    public function getActiveAdherentMandates(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('finishAt', null))
            ->andWhere(Criteria::expr()->eq('quality', null))
            ->orderBy(['beginAt' => 'DESC'])
        ;

        return $this->adherentMandates->matching($criteria);
    }

    /**
     * @return CommitteeAdherentMandate[]
     */
    public function getActiveDesignatedAdherentMandates(): array
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('finishAt', null))
            ->andWhere(Criteria::expr()->eq('quality', null))
        ;

        return $this->adherentMandates
            ->matching($criteria)
            ->filter(function (AdherentMandateInterface $mandate) {
                return $mandate instanceof CommitteeAdherentMandate;
            })
            ->toArray()
        ;
    }

    public function hasParliamentaryMandates(): bool
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('finishAt', null))
            ->andWhere(Criteria::expr()->eq('quality', null))
        ;

        $types = array_merge(MandateTypeEnum::PARLIAMENTARY_TYPES, [MandateTypeEnum::MINISTER]);

        return !$this->adherentMandates
            ->matching($criteria)
            ->filter(
                fn (AbstractAdherentMandate $mandate) => $mandate instanceof ElectedRepresentativeAdherentMandate && \in_array($mandate->mandateType, $types)
            )
            ->isEmpty()
        ;
    }

    /**
     * @return ElectedRepresentativeAdherentMandate[]
     */
    public function findElectedRepresentativeMandates(bool $active): array
    {
        return $this->adherentMandates->filter(function (AdherentMandateInterface $mandate) use ($active) {
            return $mandate instanceof ElectedRepresentativeAdherentMandate
                && (false === $active || null === $mandate->getFinishAt());
        })->toArray();
    }

    /**
     * @return ElectedRepresentativeAdherentMandate[]
     */
    public function findActifLocalMandates(): array
    {
        return $this->adherentMandates->filter(function (AdherentMandateInterface $mandate) {
            return
                $mandate instanceof ElectedRepresentativeAdherentMandate
                && null === $mandate->getFinishAt()
                && $mandate->isLocal();
        })->toArray();
    }

    /**
     * @return ElectedRepresentativeAdherentMandate[]
     */
    public function findActifNationalMandates(): array
    {
        return $this->adherentMandates->filter(function (AdherentMandateInterface $mandate) {
            return
                $mandate instanceof ElectedRepresentativeAdherentMandate
                && null === $mandate->getFinishAt()
                && !$mandate->isLocal();
        })->toArray();
    }

    public function getFilePermissions(): array
    {
        $roles = array_map(static function (string $role) {
            return str_replace('role_', '', mb_strtolower($role));
        }, $this->getRoles());

        return array_values(array_intersect(FilePermissionEnum::toArray(), $roles));
    }

    public function getProvisionalSupervisors(): Collection
    {
        return $this->provisionalSupervisors;
    }

    public function isProvisionalSupervisor(): bool
    {
        return $this->provisionalSupervisors->filter(function (ProvisionalSupervisor $provisionalSupervisor) {
            $committee = $provisionalSupervisor->getCommittee();

            return $committee->isWaitingForApproval();
        })->count() > 0;
    }

    /** @return CommitteeAdherentMandate[]|Collection */
    public function getSupervisorMandates(?bool $isProvisional = null, ?string $gender = null): Collection
    {
        return $this->adherentMandates->filter(static function (AdherentMandateInterface $mandate) use ($gender, $isProvisional) {
            return $mandate instanceof CommitteeAdherentMandate
                && null !== $mandate->getCommittee()
                && CommitteeMandateQualityEnum::SUPERVISOR === $mandate->getQuality()
                && null === $mandate->getFinishAt()
                && (null === $isProvisional || $mandate->isProvisional() === $isProvisional)
                && (null === $gender || $mandate->getGender() === $gender);
        });
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): void
    {
        $this->source = $source;
    }

    public function isRenaissanceUser(): bool
    {
        return MembershipSourceEnum::RENAISSANCE === $this->source;
    }

    public function isRenaissanceAdherent(): bool
    {
        return $this->hasTag(TagEnum::ADHERENT);
    }

    public function isRenaissanceSympathizer(): bool
    {
        return $this->hasTag(TagEnum::SYMPATHISANT);
    }

    public function getTeamMemberships(): Collection
    {
        return $this->teamMemberships;
    }

    public function getMailchimpStatus(): ?string
    {
        return $this->mailchimpStatus;
    }

    public function clean(): void
    {
        $this->mailchimpStatus = ContactStatusEnum::CLEANED;
        $this->emailStatusComment = ContactStatusEnum::CLEANED;
        $this->subscriptionTypes->clear();
    }

    #[Groups(['user_profile'])]
    public function isEmailSubscribed(): bool
    {
        return ContactStatusEnum::SUBSCRIBED === $this->mailchimpStatus;
    }

    public function hasPhoningManagerRole(): bool
    {
        return $this->phoningManagerRole;
    }

    public function setPhoningManagerRole(bool $phoningManagerRole): void
    {
        $this->phoningManagerRole = $phoningManagerRole;
    }

    public function hasPapNationalManagerRole(): bool
    {
        return $this->papNationalManagerRole;
    }

    public function setPapNationalManagerRole(bool $papNationalManagerRole): void
    {
        $this->papNationalManagerRole = $papNationalManagerRole;
    }

    public function hasPapUserRole(): bool
    {
        return $this->papUserRole;
    }

    public function setPapUserRole(bool $papUserRole): void
    {
        $this->papUserRole = $papUserRole;
    }

    public function hasZoneBasedRole(string $scope): bool
    {
        return null !== $this->findZoneBasedRole($scope);
    }

    public function findZoneBasedRole(string $scope): ?AdherentZoneBasedRole
    {
        $matched = $this->zoneBasedRoles->matching(
            Criteria::create()->where(Criteria::expr()->eq('type', $scope))
        );

        return $matched->count() ? $matched->first() : null;
    }

    public function getAuthAppCode(): ?string
    {
        return $this->authAppCode;
    }

    public function setAuthAppCode(?string $authAppCode): void
    {
        $this->authAppCode = $authAppCode;
    }

    public function getAuthAppVersion(): int
    {
        $parts = preg_split('/[.#]/', str_replace('v', '', (string) $this->authAppVersion));

        return (int) \sprintf(
            '%02d%02d%02d%02d',
            $parts[0] ?? 0,
            $parts[1] ?? 0,
            $parts[2] ?? 0,
            $parts[3] ?? 0
        );
    }

    public function setAuthAppVersion(?string $authAppVersion): void
    {
        $this->authAppVersion = $authAppVersion;
    }

    public function donatedForMembership(\DateTimeInterface $donatedAt): void
    {
        if (!$this->firstMembershipDonation || $this->firstMembershipDonation > $donatedAt) {
            $this->firstMembershipDonation = $donatedAt;
        }

        if (!$this->lastMembershipDonation || $this->lastMembershipDonation < $donatedAt) {
            $this->lastMembershipDonation = $donatedAt;
        }
    }

    public function setLastMembershipDonation(?\DateTimeInterface $date): void
    {
        $this->lastMembershipDonation = $date;
    }

    public function getLastMembershipDonation(): ?\DateTimeInterface
    {
        return $this->lastMembershipDonation;
    }

    public function setFirstMembershipDonation(?\DateTimeInterface $date): void
    {
        $this->firstMembershipDonation = $date;
    }

    public function getFirstMembershipDonation(): ?\DateTimeInterface
    {
        return $this->firstMembershipDonation;
    }

    public function isPrimoInYear(?int $year = null): bool
    {
        if (!$this->firstMembershipDonation) {
            return false;
        }

        $donationYear = (int) $this->firstMembershipDonation->format('Y');
        $yearToCheck = $year ?? (int) (new \DateTime())->format('Y');

        return $donationYear === $yearToCheck;
    }

    public function hasActiveMembership(): bool
    {
        return $this->isRenaissanceAdherent() && $this->hasTag(TagEnum::getAdherentYearTag());
    }

    public function isExclusiveMembership(): bool
    {
        return MembershipTypeEnum::EXCLUSIVE === $this->partyMembership;
    }

    public function isTerritoireProgresMembership(): bool
    {
        return MembershipTypeEnum::TERRITOIRES_PROGRES === $this->partyMembership;
    }

    public function isAgirMembership(): bool
    {
        return MembershipTypeEnum::AGIR === $this->partyMembership;
    }

    public function isModemMembership(): bool
    {
        return MembershipTypeEnum::MODEM === $this->partyMembership;
    }

    public function isOtherPartyMembership(): bool
    {
        return MembershipTypeEnum::OTHER === $this->partyMembership;
    }

    #[Groups(['profile_read'])]
    public function getOtherPartyMembership(): bool
    {
        return $this->isOtherPartyMembership();
    }

    public function isFrench(): bool
    {
        return AddressInterface::FRANCE === $this->nationality;
    }

    public function getValidCampusRegistration(): ?Registration
    {
        foreach ($this->campusRegistrations as $registration) {
            if ($registration->isValid()) {
                return $registration;
            }
        }

        return null;
    }

    public function getContributionStatus(): ?string
    {
        return $this->contributionStatus;
    }

    public function setContributionStatus(?string $contributionStatus): void
    {
        $this->contributionStatus = $contributionStatus;
    }

    public function getContributedAt(): ?\DateTime
    {
        return $this->contributedAt;
    }

    public function setContributedAt(?\DateTime $contributedAt): void
    {
        $this->contributedAt = $contributedAt;
    }

    public function getLastContribution(): ?Contribution
    {
        return $this->lastContribution;
    }

    public function setLastContribution(?Contribution $lastContribution): void
    {
        $this->lastContribution = $lastContribution;
    }

    public function getContributions(): Collection
    {
        return $this->contributions;
    }

    public function addContribution(Contribution $contribution): void
    {
        if (!$this->contributions->contains($contribution)) {
            $contribution->adherent = $this;
            $this->contributions->add($contribution);
        }
    }

    public function removeContribution(Contribution $contribution): void
    {
        $this->contributions->removeElement($contribution);
    }

    public function hasRecentContribution(): bool
    {
        $date = new \DateTime('-21 days 00:00');

        $lastContribution = $this->getLastContribution();

        return $lastContribution && $lastContribution->getCreatedAt() >= $date;
    }

    public function isContributionUpToDate(): bool
    {
        return $this->hasTag(TagEnum::ELU_COTISATION_OK_SOUMIS);
    }

    #[Groups(['adherent_elect_read'])]
    public function getContributionAmount(): ?int
    {
        $lastRevenueDeclaration = $this->getLastRevenueDeclaration();

        if ($lastRevenueDeclaration) {
            return ContributionAmountUtils::getContributionAmount($lastRevenueDeclaration->amount);
        }

        return null;
    }

    public function getPayments(): Collection
    {
        return $this->payments;
    }

    /**
     * @return Payment[]
     */
    public function getConfirmedPayments(): array
    {
        $date = new \DateTime('-40 days 00:00');

        return $this->payments->filter(function (Payment $p) use ($date) {
            return $p->isConfirmed() && $p->date >= $date;
        })->toArray();
    }

    public function addPayment(Payment $payment): void
    {
        if (!$this->payments->contains($payment)) {
            $payment->adherent = $this;
            $this->payments->add($payment);
        }
    }

    public function removePayment(Payment $payment): void
    {
        $this->payments->removeElement($payment);
    }

    public function getPaymentByOhmeId(string $ohmeId): ?Payment
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('ohmeId', $ohmeId))
        ;

        return $this->payments->matching($criteria)->count() > 0
            ? $this->payments->matching($criteria)->first()
            : null;
    }

    public function getRevenueDeclarations(): Collection
    {
        return $this->revenueDeclarations;
    }

    public function addRevenueDeclaration(int $amount): void
    {
        $this->revenueDeclarations->add(RevenueDeclaration::create($this, $amount));
    }

    #[Groups(['adherent_elect_read'])]
    #[SerializedName('elect_mandates')]
    public function getElectedRepresentativeMandates(): array
    {
        return array_values($this->findElectedRepresentativeMandates(false));
    }

    public function addAdherentMandate(AdherentMandateInterface $adherentMandate): void
    {
        if (!$this->adherentMandates->contains($adherentMandate)) {
            $this->adherentMandates->add($adherentMandate);
        }
    }

    public function removeAdherentMandate(AdherentMandateInterface $adherentMandate): void
    {
        $this->adherentMandates->removeElement($adherentMandate);
    }

    /**
     * @param ElectedRepresentativeAdherentMandate[]|iterable $adherentMandates
     */
    public function setElectedRepresentativeMandates(iterable $adherentMandates): void
    {
        foreach ($this->adherentMandates as $adherentMandate) {
            if ($adherentMandate instanceof ElectedRepresentativeAdherentMandate) {
                $this->removeAdherentMandate($adherentMandate);
            }
        }

        foreach ($adherentMandates as $adherentMandate) {
            $adherentMandate->setAdherent($this);
            $this->addAdherentMandate($adherentMandate);
        }
    }

    #[Groups(['adherent_elect_read'])]
    public function getLastRevenueDeclaration(): ?RevenueDeclaration
    {
        return $this->revenueDeclarations->first() ?: null;
    }

    public function hasTag(string $tag): bool
    {
        return TagEnum::includesTag($tag, $this->tags ?? []);
    }

    public function updateFromMembershipRequest(MembershipRequest $membershipRequest): void
    {
        if (!$this->isCertified()) {
            $this->firstName = $membershipRequest->firstName;
            $this->lastName = $membershipRequest->lastName;
            $this->nationality = $membershipRequest->nationality;
            $this->gender = $membershipRequest->civility;
        }

        $this->phone = $membershipRequest->phone ?? $this->phone;
    }

    public function isV2(): bool
    {
        return $this->v2;
    }

    public function setV2(bool $value): void
    {
        $this->v2 = $value;
    }

    public function isEligibleForMembershipPayment(): bool
    {
        return !$this->isOtherPartyMembership();
    }

    public function finishAdhesionStep(string $step): void
    {
        $this->finishedAdhesionSteps = array_unique(array_merge($this->finishedAdhesionSteps, [$step]));
    }

    public function isFullyCompletedAdhesion(): bool
    {
        return empty(array_diff(AdhesionStepEnum::all($this->isRenaissanceAdherent()), $this->finishedAdhesionSteps));
    }

    public function isFullyCompletedBesoinDEuropeInscription(): bool
    {
        return empty(array_diff(AdhesionStepEnum::allBesoinDEurope(), $this->finishedAdhesionSteps));
    }

    public function getFinishedAdhesionSteps(): array
    {
        return $this->finishedAdhesionSteps;
    }

    public function hasFinishedAdhesionStep(string $step): bool
    {
        return \in_array($step, $this->finishedAdhesionSteps, true);
    }

    public function isBesoinDEuropeUser(): bool
    {
        return MembershipSourceEnum::BESOIN_D_EUROPE === $this->source;
    }

    public function getImagePath(): string
    {
        return $this->imageName ? \sprintf('images/profile/%s', $this->getImageName()) : '';
    }

    /**
     * @return AdherentStaticLabel[]|Collection
     */
    public function getStaticLabels(): Collection
    {
        return $this->staticLabels;
    }

    public function addStaticLabel(AdherentStaticLabel $adherentStaticLabel): void
    {
        if (!$this->staticLabels->contains($adherentStaticLabel)) {
            $this->staticLabels->add($adherentStaticLabel);
        }
    }

    public function removeStaticLabel(AdherentStaticLabel $adherentStaticLabel): void
    {
        $this->staticLabels->removeElement($adherentStaticLabel);
    }

    public function isPresidentOfAgora(): bool
    {
        return $this->presidentOfAgoras->count() > 0;
    }

    public function isGeneralSecretaryOfAgora(): bool
    {
        return $this->generalSecretaryOfAgoras->count() > 0;
    }
}

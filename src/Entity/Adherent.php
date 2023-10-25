<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Address\AddressInterface;
use App\Address\PostAddressFactory;
use App\Adherent\Contribution\ContributionAmountUtils;
use App\Adherent\LastLoginGroupEnum;
use App\Adherent\Tag\TagEnum;
use App\AdherentProfile\AdherentProfile;
use App\Collection\AdherentCharterCollection;
use App\Collection\CertificationRequestCollection;
use App\Collection\CommitteeMembershipCollection;
use App\Collection\ZoneCollection;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\AdherentCharter\AdherentCharterInterface;
use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\AdherentMandate\NationalCouncilAdherentMandate;
use App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate;
use App\Entity\BoardMember\BoardMember;
use App\Entity\Campus\Registration;
use App\Entity\Contribution\Contribution;
use App\Entity\Contribution\Payment;
use App\Entity\Contribution\RevenueDeclaration;
use App\Entity\Filesystem\FilePermissionEnum;
use App\Entity\Geo\Zone;
use App\Entity\Instance\AdherentInstanceQuality;
use App\Entity\Instance\InstanceQuality;
use App\Entity\ManagedArea\CandidateManagedArea;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\DelegatedAccessEnum;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Entity\Team\Member;
use App\Entity\TerritorialCouncil\PoliticalCommitteeMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Entity\ThematicCommunity\ThematicCommunity;
use App\Exception\AdherentAlreadyEnabledException;
use App\Exception\AdherentException;
use App\Exception\AdherentTokenException;
use App\Geocoder\GeoPointInterface;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Mailchimp\Contact\MailchimpCleanableContactInterface;
use App\Membership\ActivityPositionsEnum;
use App\Membership\MembershipRequest\MembershipInterface;
use App\Membership\MembershipRequest\PlatformMembershipRequest;
use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use App\Membership\MembershipSourceEnum;
use App\OAuth\Model\User as InMemoryOAuthUser;
use App\Renaissance\Membership\Admin\AdherentCreateCommand;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;
use App\Utils\AreaUtils;
use App\Validator\TerritorialCouncil\UniqueTerritorialCouncilMember;
use App\Validator\UniqueMembership;
use App\Validator\ZoneBasedRoles as AssertZoneBasedRoles;
use App\ValueObject\Genders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use League\OAuth2\Server\Entities\UserEntityInterface;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="adherents")
 * @ORM\Entity(repositoryClass="App\Repository\AdherentRepository")
 * @ORM\EntityListeners({
 *     "App\EntityListener\RevokeReferentTeamMemberRolesListener",
 * })
 *
 * @UniqueEntity(fields={"nickname"}, groups={"anonymize"})
 * @UniqueMembership(groups={"Admin"})
 *
 * @UniqueTerritorialCouncilMember(qualities={"referent", "referent_jam"})
 *
 * @ApiResource(
 *     routePrefix="/v3",
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"adherent_elect_read"},
 *         },
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'contacts')",
 *     },
 *     itemOperations={
 *         "get_elect": {
 *             "path": "/adherents/{uuid}/elect",
 *             "method": "GET",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative')",
 *         },
 *         "put_elect": {
 *             "path": "/adherents/{uuid}/elect",
 *             "method": "PUT",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "denormalization_context": {
 *                 "groups": {"adherent_elect_update"},
 *             },
 *             "validation_groups": {"adherent_elect_update"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative')",
 *         },
 *     },
 *     collectionOperations={},
 * )
 */
class Adherent implements UserInterface, UserEntityInterface, GeoPointInterface, EncoderAwareInterface, MembershipInterface, ReferentTaggableEntity, ZoneableEntity, EntityMediaInterface, EquatableInterface, UuidEntityInterface, MailchimpCleanableContactInterface, PasswordAuthenticatedUserInterface, EntityAdministratorBlameableInterface
{
    use EntityCrudTrait;
    use EntityIdentityTrait;
    use EntityPersonNameTrait;
    use EntityPostAddressTrait;
    use LazyCollectionTrait;
    use EntityReferentTagTrait;
    use EntityZoneTrait;
    use EntityUTMTrait;
    use EntityAdministratorBlameableTrait;

    public const ENABLED = 'ENABLED';
    public const TO_DELETE = 'TO_DELETE';
    public const DISABLED = 'DISABLED';

    /**
     * @ORM\Column(length=25, unique=true, nullable=true)
     *
     * @Assert\Length(allowEmptyString=true, min=3, max=25, groups={"Default", "anonymize"})
     * @Assert\Regex(pattern="/^[a-z0-9 _-]+$/i", message="adherent.nickname.invalid_syntax", groups={"anonymize"})
     * @Assert\Regex(pattern="/^[a-zÀ-ÿ0-9 .!_-]+$/i", message="adherent.nickname.invalid_extended_syntax")
     *
     * @Groups({"user_profile"})
     */
    private $nickname;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private bool $nicknameUsed = false;

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
     * @Groups({
     *     "api_candidacy_read",
     *     "profile_read",
     *     "phoning_campaign_call_read",
     *     "phoning_campaign_history_read_list",
     *     "pap_campaign_history_read_list",
     *     "pap_campaign_replies_list",
     *     "phoning_campaign_replies_list",
     *     "survey_replies_list",
     *     "adherent_change_diff",
     *     "committee_candidacy:read",
     *     "committee_election:read",
     * })
     * @Assert\NotBlank(message="common.gender.not_blank", groups={"adhesion_complete_profile"})
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     groups={"adhesion_complete_profile"}
     * )
     */
    private $gender;

    /**
     * @ORM\Column(length=80, nullable=true)
     *
     * @Groups({"profile_read"})
     */
    private $customGender;

    /**
     * @ORM\Column(unique=true)
     *
     * @Groups({"user_profile", "profile_read", "adherent_change_diff", "elected_representative_read", "adherent_autocomplete", "my_team_read_list"})
     */
    private $emailAddress;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @Groups({"profile_read", "phoning_campaign_call_read", "elected_representative_read"})
     *
     * @AssertPhoneNumber(message="common.phone_number.invalid", options={"groups": {"additional_info"}})
     */
    private $phone;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $phoneVerifiedAt = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     *
     * @Groups({"profile_read", "adherent_change_diff"})
     *
     * @Assert\NotBlank(message="adherent.birthdate.not_blank", groups={"additional_info"})
     * @Assert\Range(max="-15 years", maxMessage="adherent.birthdate.minimum_required_age", groups={"additional_info"})
     */
    private $birthdate;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"profile_read"})
     */
    private $position;

    /**
     * @ORM\Column(length=10, options={"default": "DISABLED"})
     *
     * @Groups({"adherent_change_diff"})
     */
    private string $status = self::DISABLED;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups({"adherent_autocomplete"})
     */
    private $registeredAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $membershipRemindedAt;

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
     * @ORM\Column(nullable=true)
     *
     * @Groups({"adherent_change_diff"})
     */
    private ?string $lastLoginGroup = null;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Groups({"adherent_change_diff"})
     */
    private $interests = [];

    /**
     * @var SubscriptionType[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="SubscriptionType", cascade={"persist"})
     *
     * @Groups({"profile_read"})
     */
    private $subscriptionTypes;

    /**
     * @var ReferentManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ReferentManagedArea", cascade={"all"}, orphanRemoval=true)
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
     * @ORM\OneToMany(targetEntity="App\Entity\AdherentZoneBasedRole", fetch="EAGER", mappedBy="adherent", cascade={"persist"}, orphanRemoval=true)
     *
     * @AssertZoneBasedRoles
     */
    private Collection $zoneBasedRoles;

    /**
     * @var CoordinatorManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\CoordinatorManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $coordinatorCommitteeArea;

    /**
     * @var ProcurationManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ProcurationManagedArea", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid
     */
    private $procurationManagedArea;

    /**
     * @var AssessorManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\AssessorManagedArea", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid
     */
    private $assessorManagedArea;

    /**
     * @var AssessorRoleAssociation|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\AssessorRoleAssociation", cascade={"all"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $assessorRole;

    /**
     * @var BoardMember|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\BoardMember\BoardMember", mappedBy="adherent", cascade={"all"}, orphanRemoval=true)
     */
    private $boardMember;

    /**
     * @var JecouteManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\JecouteManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $jecouteManagedArea;

    /**
     * @var TerritorialCouncilMembership|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncilMembership", mappedBy="adherent", cascade={"all"}, orphanRemoval=true)
     *
     * @Groups({"adherent_change_diff"})
     */
    private $territorialCouncilMembership;

    /**
     * @var AdherentInstanceQuality[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Instance\AdherentInstanceQuality", mappedBy="adherent", cascade={"all"}, orphanRemoval=true)
     */
    private $instanceQualities;

    /**
     * @var PoliticalCommitteeMembership|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\TerritorialCouncil\PoliticalCommitteeMembership", mappedBy="adherent", cascade={"all"}, orphanRemoval=true)
     */
    private $politicalCommitteeMembership;

    /**
     * @var CommitteeMembership[]|Collection
     *
     * @ORM\OneToMany(targetEntity="CommitteeMembership", mappedBy="adherent", cascade={"remove"})
     */
    private $memberships;

    /**
     * @var Committee[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Committee", mappedBy="animator", fetch="EXTRA_LAZY")
     */
    private $animatorCommittees;

    /**
     * @var CommitteeFeedItem[]|Collection|iterable
     *
     * @ORM\OneToMany(targetEntity="CommitteeFeedItem", mappedBy="author", cascade={"remove"})
     */
    private $committeeFeedItems;

    /**
     * @var District|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\District", cascade={"persist"})
     */
    private $managedDistrict;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Groups({"profile_read"})
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
     * Activation token was already sent after 48h of registration
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $remindSent = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public ?\DateTime $globalNotificationSentAt = null;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Groups({"adherent_elect_read"})
     */
    private array $mandates = [];

    /**
     * @var Media|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Media", cascade={"persist"})
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
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?facebook.com\/#", message="legislative_candidate.facebook_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     *
     * @Groups({"profile_read"})
     */
    private $facebookPageUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?twitter.com\/#", message="legislative_candidate.twitter_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     *
     * @Groups({"profile_read"})
     */
    private $twitterPageUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?linkedin.com\/#", message="legislative_candidate.linkedin_page_url.invalid", groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     *
     * @Groups({"profile_read"})
     */
    private $linkedinPageUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups="Admin")
     * @Assert\Length(max=255, groups="Admin")
     *
     * @Groups({"profile_read"})
     */
    private $telegramPageUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Groups({"profile_read"})
     */
    private $job;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Groups({"profile_read"})
     */
    private $activityArea;

    /**
     * @var string|null
     *
     * @ORM\Column(length=2, nullable=true)
     *
     * @Groups({"profile_read"})
     *
     * @Assert\NotBlank(groups={"adhesion_complete_profile"})
     * @Assert\Country(message="common.nationality.invalid", groups={"adhesion_complete_profile"})
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
     * @var SenatorialCandidateManagedArea|null
     *
     * @Assert\Valid
     * @ORM\OneToOne(targetEntity="App\Entity\SenatorialCandidateManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $senatorialCandidateManagedArea;

    /**
     * @var CandidateManagedArea|null
     *
     * @Assert\Valid
     * @ORM\OneToOne(targetEntity="App\Entity\ManagedArea\CandidateManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $candidateManagedArea;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private bool $nationalRole = false;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private bool $nationalCommunicationRole = false;

    /**
     * @var Collection|AdherentCharterInterface[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\AdherentCharter\AbstractAdherentCharter", mappedBy="adherent", cascade={"all"})
     */
    private $charters;

    /**
     * @var SenatorArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\SenatorArea", cascade={"all"}, orphanRemoval=true)
     */
    private $senatorArea;

    /**
     * @var ConsularManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ConsularManagedArea", cascade={"all"}, orphanRemoval=true)
     */
    private $consularManagedArea;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $electionResultsReporter = false;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"adherent_change_diff"})
     */
    private $certifiedAt;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $source;

    /**
     * @var CertificationRequest[]|Collection
     *
     * @ORM\OneToMany(targetEntity=CertificationRequest::class, mappedBy="adherent", cascade={"all"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"createdAt": "ASC"})
     */
    private $certificationRequests;

    /**
     * @var DelegatedAccess[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\MyTeam\DelegatedAccess", mappedBy="delegated", cascade={"all"})
     */
    private $receivedDelegatedAccesses;

    /**
     * @var AdherentCommitment
     *
     * @ORM\OneToOne(targetEntity="App\Entity\AdherentCommitment", mappedBy="adherent", cascade={"all"})
     */
    private $commitment;

    /**
     * @var ThematicCommunity[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ThematicCommunity\ThematicCommunity")
     */
    private $handledThematicCommunities;

    /**
     * @var AdherentMandateInterface[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\AdherentMandate\AbstractAdherentMandate",
     *     mappedBy="adherent",
     *     fetch="EXTRA_LAZY",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     *
     * @Assert\Valid
     */
    private $adherentMandates;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $notifiedForElection = false;

    /**
     * @var ProvisionalSupervisor[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ProvisionalSupervisor", mappedBy="adherent", cascade={"all"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $provisionalSupervisors;

    /**
     * @var Member[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Team\Member", mappedBy="adherent", fetch="EXTRA_LAZY")
     */
    private $teamMemberships;

    /**
     * @ORM\Embedded(class="App\Entity\PostAddress", columnPrefix="address_")
     *
     * @var PostAddress
     *
     * @Groups({"profile_read"})
     */
    protected $postAddress;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $voteInspector = false;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $mailchimpStatus = ContactStatusEnum::SUBSCRIBED;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $phoningManagerRole = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $papNationalManagerRole = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $papUserRole = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Zone $activismZone = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $lastMembershipDonation = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Assert\Expression(
     *     expression="!(value == false and this.isTerritoireProgresMembership() == false and this.isAgirMembership() == false)",
     *     message="adherent.exclusive_membership.no_accepted",
     *     groups={"adhesion_complete_profile"}
     * )
     */
    private bool $exclusiveMembership = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $territoireProgresMembership = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $agirMembership = false;

    private ?string $authAppCode = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $emailStatusComment = null;

    /**
     * @var Registration[]|Collection
     *
     * @ORM\OneToMany(targetEntity=Registration::class, mappedBy="adherent", cascade={"all"}, fetch="EXTRA_LAZY")
     */
    private Collection $campusRegistrations;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"adherent_elect_read"})
     */
    private ?string $contributionStatus = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Groups({"adherent_elect_read", "adherent_elect_update"})
     *
     * @Assert\Expression("!value || (!this.findActifNationalMandates() and this.findActifLocalMandates())", message="adherent.elect.exempt_invalid_status", groups={"adherent_elect_update"})
     */
    public bool $exemptFromCotisation = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"adherent_elect_read"})
     */
    private ?\DateTime $contributedAt = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Contribution\Contribution")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?Contribution $lastContribution = null;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Contribution\Contribution",
     *     mappedBy="adherent",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EXTRA_LAZY"
     * )
     */
    private Collection $contributions;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Contribution\Payment",
     *     mappedBy="adherent",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\OrderBy({"date": "DESC"})
     *
     * @Groups({"adherent_elect_read"})
     */
    private Collection $payments;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Contribution\RevenueDeclaration",
     *     mappedBy="adherent",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\OrderBy({"createdAt": "DESC"})
     */
    private Collection $revenueDeclarations;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    public array $tags = [];

    public function __construct()
    {
        $this->memberships = new ArrayCollection();
        $this->animatorCommittees = new ArrayCollection();
        $this->subscriptionTypes = new ArrayCollection();
        $this->zones = new ZoneCollection();
        $this->charters = new AdherentCharterCollection();
        $this->certificationRequests = new ArrayCollection();
        $this->receivedDelegatedAccesses = new ArrayCollection();
        $this->handledThematicCommunities = new ArrayCollection();
        $this->adherentMandates = new ArrayCollection();
        $this->provisionalSupervisors = new ArrayCollection();
        $this->instanceQualities = new ArrayCollection();
        $this->teamMemberships = new ArrayCollection();
        $this->zoneBasedRoles = new ArrayCollection();
        $this->referentTags = new ArrayCollection();
        $this->campusRegistrations = new ArrayCollection();
        $this->contributions = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->revenueDeclarations = new ArrayCollection();
    }

    public static function createBlank(
        string $gender,
        string $firstName,
        string $lastName,
        string $nationality,
        PostAddress $postAddress,
        string $email,
        ?PhoneNumber $phone,
        ?\DateTimeInterface $birthdate,
        bool $exclusiveMembership = false,
        bool $territoiresProgresMembership = false,
        bool $agirMembership = false,
        \DateTime $registeredAt = null
    ): self {
        $adherent = new self();

        $adherent->uuid = Uuid::uuid4();
        $adherent->gender = $gender;
        $adherent->firstName = $firstName;
        $adherent->lastName = $lastName;
        $adherent->nationality = $nationality;
        $adherent->postAddress = $postAddress;
        $adherent->emailAddress = $email;
        $adherent->phone = $phone;
        $adherent->birthdate = $birthdate;
        $adherent->exclusiveMembership = $exclusiveMembership;
        $adherent->territoireProgresMembership = $territoiresProgresMembership;
        $adherent->agirMembership = $agirMembership;
        $adherent->registeredAt = $registeredAt ?? new \DateTime('now');

        $adherent->password = Uuid::uuid4();

        return $adherent;
    }

    public static function create(
        UuidInterface $uuid,
        string $emailAddress,
        string $password,
        ?string $gender,
        string $firstName,
        string $lastName,
        ?\DateTimeInterface $birthDate,
        ?string $position,
        PostAddress $postAddress,
        PhoneNumber $phone = null,
        string $nickname = null,
        bool $nicknameUsed = false,
        string $status = self::DISABLED,
        string $registeredAt = 'now',
        ?array $referentTags = [],
        ?array $mandates = [],
        string $nationality = null,
        string $customGender = null
    ): self {
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
        $adherent->registeredAt = new \DateTime($registeredAt);
        $adherent->referentTags = new ArrayCollection($referentTags);
        $adherent->mandates = $mandates ?? [];
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

    /**
     * @Groups({"user_profile"})
     */
    public function isElected(): bool
    {
        return $this->getMandates() && \count($this->getMandates()) > 0;
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

        if ($this->isPresidentDepartmentalAssembly()) {
            $roles[] = 'ROLE_PRESIDENT_DEPARTMENTAL_ASSEMBLY';
        }

        if ($this->isSenator()) {
            $roles[] = 'ROLE_SENATOR';
        }

        if ($this->isConsular()) {
            $roles[] = 'ROLE_CONSULAR';
        }

        if ($this->hasFormationSpaceAccess()) {
            $roles[] = 'ROLE_FORMATION_SPACE';
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

        if ($this->isProcurationManager()) {
            $roles[] = 'ROLE_PROCURATION_MANAGER';
        }

        if ($this->isAssessorManager()) {
            $roles[] = 'ROLE_ASSESSOR_MANAGER';
        }

        if ($this->isAssessor()) {
            $roles[] = 'ROLE_ASSESSOR';
        }

        if ($this->isJecouteManager()) {
            $roles[] = 'ROLE_JECOUTE_MANAGER';
        }

        if ($this->isLegislativeCandidate()) {
            $roles[] = 'ROLE_LEGISLATIVE_CANDIDATE';
        }

        if ($this->isBoardMember()) {
            $roles[] = 'ROLE_BOARD_MEMBER';
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

        if ($this->isSenatorialCandidate()) {
            $roles[] = 'ROLE_SENATORIAL_CANDIDATE';
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

        if ($this->isThematicCommunityChief()) {
            $roles[] = 'ROLE_THEMATIC_COMMUNITY_CHIEF';
        }

        if ($this->hasNationalCouncilQualities()) {
            $roles[] = 'ROLE_NATIONAL_COUNCIL_MEMBER';
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

        if ($this->isCorrespondent()) {
            $roles[] = 'ROLE_CORRESPONDENT';
        }

        if ($this->isRenaissanceUser()) {
            $roles[] = 'ROLE_RENAISSANCE_USER';
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
        if ($this->isReferent()) {
            return 'REFERENT';
        }

        if ($this->isSupervisor() || $this->isHost()) {
            return 'HOST';
        }

        return 'ADHERENT';
    }

    public function hasAdvancedPrivileges(): bool
    {
        return $this->isReferent()
            || $this->isDelegatedReferent()
            || $this->isRegionalCoordinator()
            || $this->isProcurationManager()
            || $this->isAssessorManager()
            || $this->isAssessor()
            || $this->isJecouteManager()
            || $this->isSupervisor()
            || $this->isHost()
            || $this->isBoardMember()
            || $this->isDeputy()
            || $this->isDelegatedDeputy()
            || $this->isSenator()
            || $this->isDelegatedSenator()
            || $this->isElectionResultsReporter()
            || $this->isSenatorialCandidate()
            || $this->isHeadedRegionalCandidate()
            || $this->isLeaderRegionalCandidate()
            || $this->isDepartmentalCandidate()
            || $this->isDelegatedCandidate()
            || $this->isLegislativeCandidate()
            || $this->isThematicCommunityChief()
            || $this->isCorrespondent()
            || $this->isPresidentDepartmentalAssembly()
            || $this->isDelegatedPresidentDepartmentalAssembly()
            || $this->isAnimator()
            || $this->isDelegatedAnimator();
    }

    public function getPassword(): ?string
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

    public function setPhone(PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getGenderName(): ?string
    {
        return array_search($this->gender, Genders::CHOICES);
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
        return AddressInterface::FRANCE === strtoupper($this->getCountry()) && AreaUtils::PREFIX_POSTALCODE_PARIS_DISTRICTS === substr($this->getPostalCode(), 0, 2);
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

    /**
     * @Groups({
     *     "export",
     *     "phoning_campaign_history_read_list",
     *     "pap_campaign_history_read_list",
     *     "pap_campaign_replies_list",
     *     "phoning_campaign_replies_list",
     *     "survey_replies_list",
     * })
     */
    public function getAge(): ?int
    {
        return $this->birthdate ? $this->birthdate->diff(new \DateTime())->y : null;
    }

    public function isMinor(\DateTime $date = null): bool
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
            throw new \InvalidArgumentException(sprintf('Invalid position "%s", known positions are "%s".', $position, implode('", "', ActivityPositionsEnum::ALL)));
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

    public function setMembershipReminded(): void
    {
        $this->membershipRemindedAt = new \DateTime();
    }

    public function isMembershipReminded(): bool
    {
        return null !== $this->membershipRemindedAt;
    }

    public function changePassword(string $newPassword): void
    {
        $this->password = $newPassword;
    }

    /**
     * @Groups({"adherent_change_diff"})
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
        $this->enable($timestamp);
    }

    public function enable(string $timestamp = 'now'): void
    {
        $this->status = self::ENABLED;
        $this->activatedAt = new \DateTime($timestamp);
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

        if (!$this->postAddress->equals($postAddress)) {
            $this->postAddress = $postAddress;
        }
    }

    /**
     * @param PlatformMembershipRequest|RenaissanceMembershipRequest $membership
     */
    public function updateMembership(MembershipInterface $membership, PostAddress $postAddress): void
    {
        if (!$this->isCertified()) {
            $this->customGender = $membership->getCustomGender();
            $this->gender = $membership->getGender();
            $this->firstName = $membership->firstName;
            $this->lastName = $membership->lastName;
            $this->birthdate = clone $membership->getBirthdate();
        }

        $this->position = $membership->position;
        $this->phone = $membership->getPhone();
        $this->nationality = $membership->nationality;

        if (!$this->postAddress->equals($postAddress)) {
            $this->postAddress = $postAddress;
        }

        if ($membership instanceof PlatformMembershipRequest) {
            $this->mandates = $membership->getMandates();
        }
    }

    public function updateMembershipFromAdherentRequest(AdherentRequest $adherentRequest): void
    {
        if (!$this->isCertified()) {
            $this->firstName = $adherentRequest->firstName;
            $this->lastName = $adherentRequest->lastName;
        }

        if (!$this->postAddress->equals($adherentRequest->getPostAddressModel())) {
            $this->postAddress = $adherentRequest->getPostAddressModel();
        }

        if ($adherentRequest->password) {
            $this->password = $adherentRequest->password;
        }
    }

    public function updateMembershipFormAdminAdherentCreateCommand(
        AdherentCreateCommand $command,
        Administrator $administrator
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
        $this->exclusiveMembership = $command->isExclusiveMembership();
        $this->territoireProgresMembership = $command->isTerritoiresProgresMembership();
        $this->agirMembership = $command->isAgirMembership();
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
        \DateTimeInterface $subscriptionDate = null
    ): CommitteeMembership {
        return $this->joinCommittee($committee, CommitteeMembership::COMMITTEE_HOST, $subscriptionDate ?? new \DateTime());
    }

    /**
     * Joins a committee as a simple FOLLOWER privileged person.
     */
    public function followCommittee(
        Committee $committee,
        \DateTimeInterface $subscriptionDate = null,
        CommitteeMembershipTriggerEnum $trigger = null
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
        CommitteeMembershipTriggerEnum $trigger = null
    ): CommitteeMembership {
        $committee->updateMembersCount(true, $this->isRenaissanceUser(), $this->isRenaissanceAdherent());

        return CommitteeMembership::createForAdherent($committee, $this, $privilege, $subscriptionDate, $trigger);
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
     * @Groups({"export"})
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

    public function setManagedArea(?ReferentManagedArea $managedArea): void
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
     * @Groups({"referent"})
     *
     * @return string[]
     */
    public function getManagedAreaTagCodes(): array
    {
        return $this->getManagedArea()
            ? $this->getManagedArea()->getReferentTagCodes()
            : [];
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

    public function setReferent(array $tags, string $markerLatitude = null, string $markerLongitude = null): void
    {
        $this->managedArea = new ReferentManagedArea($tags, $markerLatitude, $markerLongitude);
    }

    public function isReferent(): bool
    {
        return $this->managedArea instanceof ReferentManagedArea
            && !$this->managedArea->getTags()->isEmpty();
    }

    public function isDelegatedReferent(): bool
    {
        return \count($this->getReceivedDelegatedAccessOfType('referent')) > 0;
    }

    public function isCoReferent(): bool
    {
        return $this->referentTeamMember instanceof ReferentTeamMember;
    }

    public function isLimitedCoReferent(): bool
    {
        return $this->isCoReferent() && $this->referentTeamMember->isLimited();
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

    public function getTerritorialCouncilMembership(): ?TerritorialCouncilMembership
    {
        return $this->territorialCouncilMembership;
    }

    public function setTerritorialCouncilMembership(?TerritorialCouncilMembership $territorialCouncilMembership): void
    {
        $this->territorialCouncilMembership = $territorialCouncilMembership;

        if ($territorialCouncilMembership) {
            $this->territorialCouncilMembership->setAdherent($this);
        }
    }

    public function hasTerritorialCouncilMembership(): bool
    {
        return $this->territorialCouncilMembership instanceof TerritorialCouncilMembership;
    }

    public function isTerritorialCouncilMember(): bool
    {
        return $this->territorialCouncilMembership instanceof TerritorialCouncilMembership
            && $this->territorialCouncilMembership->getTerritorialCouncil()->isActive();
    }

    public function isTerritorialCouncilPresident(): bool
    {
        return $this->isTerritorialCouncilMember()
            && $this->territorialCouncilMembership->isPresident();
    }

    public function revokeTerritorialCouncilMembership(): void
    {
        if (!$this->territorialCouncilMembership) {
            return;
        }

        $this->territorialCouncilMembership->revoke();
        $this->territorialCouncilMembership = null;
    }

    public function getPoliticalCommitteeMembership(): ?PoliticalCommitteeMembership
    {
        return $this->politicalCommitteeMembership;
    }

    public function setPoliticalCommitteeMembership(?PoliticalCommitteeMembership $politicalCommitteeMembership): void
    {
        $this->politicalCommitteeMembership = $politicalCommitteeMembership;

        if ($politicalCommitteeMembership) {
            $this->politicalCommitteeMembership->setAdherent($this);
        }
    }

    public function hasPoliticalCommitteeMembership(): bool
    {
        return $this->politicalCommitteeMembership instanceof PoliticalCommitteeMembership;
    }

    public function isPoliticalCommitteeMember(): bool
    {
        return $this->politicalCommitteeMembership instanceof PoliticalCommitteeMembership
            && $this->politicalCommitteeMembership->getPoliticalCommittee()->isActive();
    }

    public function revokePoliticalCommitteeMembership(): void
    {
        if (!$this->politicalCommitteeMembership) {
            return;
        }

        $this->politicalCommitteeMembership->revoke();
        $this->politicalCommitteeMembership = null;
    }

    public function isMayorOrLeader(): bool
    {
        return $this->politicalCommitteeMembership
            && $this->politicalCommitteeMembership->hasOneOfQualities([TerritorialCouncilQualityEnum::MAYOR, TerritorialCouncilQualityEnum::LEADER]);
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

    public function isCoordinatorCommitteeSector(): bool
    {
        return $this->coordinatorCommitteeArea && $this->coordinatorCommitteeArea->getCodes();
    }

    public function getJecouteManagedArea(): ?JecouteManagedArea
    {
        return $this->jecouteManagedArea;
    }

    public function setJecouteManagedZone(Zone $zone = null): void
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

    public function getCommitteeV2Membership(): ?CommitteeMembership
    {
        return current($this->getMemberships()->getCommitteeV2Memberships()) ?: null;
    }

    public function hasVotingCommitteeMembership(): bool
    {
        return null !== $this->getMemberships()->getVotingCommitteeMembership();
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

    public function isBasicAdherent(): bool
    {
        return $this->isAdherent()
            && !$this->isHost()
            && !$this->isSupervisor()
            && !$this->isReferent()
            && !$this->isBoardMember()
            && !$this->isDeputy()
            && !$this->isSenator();
    }

    public function isHost(): bool
    {
        return $this->getMemberships()->countCommitteeHostMemberships() >= 1;
    }

    public function isHostOf(Committee $committee): bool
    {
        if (!$membership = $this->getMembershipFor($committee)) {
            return false;
        }

        return $membership->isHostMember();
    }

    public function isSupervisor(bool $isProvisional = null): bool
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

    public function isSupervisorOf(Committee $committee, bool $isProvisional = null): bool
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

    /**
     * @Groups({"user_profile"})
     */
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

    /**
     * @Groups({"adherent_change_diff"})
     */
    public function getReferentTagCodes(): array
    {
        return array_map(function (ReferentTag $tag) { return $tag->getCode(); }, $this->referentTags->toArray());
    }

    public function getCoordinatorCommitteeArea(): ?CoordinatorManagedArea
    {
        return $this->coordinatorCommitteeArea;
    }

    public function setCoordinatorCommitteeArea(?CoordinatorManagedArea $coordinatorCommitteeArea): void
    {
        $this->coordinatorCommitteeArea = $coordinatorCommitteeArea;
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

    public function setNationality(?string $nationality)
    {
        $this->nationality = $nationality;
    }

    /**
     * @Groups({"export", "adherent_change_diff"})
     */
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
        $this->mailchimpStatus = $value ? ContactStatusEnum::UNSUBSCRIBED : ContactStatusEnum::SUBSCRIBED;
    }

    private function isAdherentMessageRedactor(array $roles): bool
    {
        return
            array_intersect($roles, [
                'ROLE_REFERENT',
                'ROLE_DEPUTY',
                'ROLE_HOST',
                'ROLE_SUPERVISOR',
                'ROLE_ANIMATOR',
                'ROLE_SENATOR',
                'ROLE_LEGISLATIVE_CANDIDATE',
                'ROLE_PRESIDENT_DEPARTMENTAL_ASSEMBLY',
            ])
            || $this->isCandidate()
            || $this->isDelegatedCandidate()
            || $this->isCorrespondent()
            || $this->isRegionalCoordinator()
            || $this->isRegionalDelegate()
            || $this->hasDelegatedAccess(DelegatedAccess::ACCESS_MESSAGES)
            || $this->hasDelegatedScopeFeature(FeatureEnum::MESSAGES);
    }

    public function __clone()
    {
        $this->subscriptionTypes = new ArrayCollection($this->subscriptionTypes->toArray());
        $this->territorialCouncilMembership = $this->territorialCouncilMembership ? clone $this->territorialCouncilMembership : null;
        $this->postAddress = clone $this->postAddress;
    }

    /**
     * @Groups({"user_profile"})
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

        return $roles;
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

    public function hasFormationSpaceAccess(): bool
    {
        return
            $this->isHost()
            || $this->isSupervisor()
            || $this->isReferent();
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

    public function isDelegatedSenator(): bool
    {
        return \count($this->getReceivedDelegatedAccessOfType('senator')) > 0;
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

    public function getCertifiedAt(): ?\DateTime
    {
        return $this->certifiedAt;
    }

    /**
     * @Groups({"user_profile", "profile_read"})
     */
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

    public function hasDelegatedFromUser(self $delegator, string $access = null): bool
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

    public function isSenatorialCandidate(): bool
    {
        return $this->senatorialCandidateManagedArea instanceof SenatorialCandidateManagedArea;
    }

    public function getSenatorialCandidateManagedArea(): ?SenatorialCandidateManagedArea
    {
        return $this->senatorialCandidateManagedArea;
    }

    public function setSenatorialCandidateManagedArea(
        ?SenatorialCandidateManagedArea $senatorialCandidateManagedArea
    ): void {
        $this->senatorialCandidateManagedArea = $senatorialCandidateManagedArea;
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

    public function isPresidentDepartmentalAssembly(): bool
    {
        return $this->hasZoneBasedRole(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);
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
    public function getRegionalDelegateZones(): array
    {
        return $this->isRegionalDelegate() ? $this->findZoneBasedRole(ScopeEnum::REGIONAL_DELEGATE)->getZones()->toArray() : [];
    }

    /**
     * @return Zone[]
     */
    public function getPresidentDepartmentalAssemblyZones(): array
    {
        return $this->isPresidentDepartmentalAssembly() ? $this->findZoneBasedRole(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY)->getZones()->toArray() : [];
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

    public function getCommitment(): ?AdherentCommitment
    {
        return $this->commitment;
    }

    public function setCommitment(AdherentCommitment $commitment): void
    {
        $this->commitment = $commitment;
    }

    public function isVoteInspector(): bool
    {
        return $this->voteInspector;
    }

    public function setVoteInspector(bool $voteInspector): void
    {
        $this->voteInspector = $voteInspector;
    }

    public function getHandledThematicCommunities(): Collection
    {
        return $this->handledThematicCommunities;
    }

    public function setHandledThematicCommunities(Collection $handledThematicCommunities): void
    {
        $this->handledThematicCommunities = $handledThematicCommunities;
    }

    public function addHandledThematicCommunity(ThematicCommunity $thematicCommunity): void
    {
        if (!$this->handledThematicCommunities->contains($thematicCommunity)) {
            $this->handledThematicCommunities->add($thematicCommunity);
        }
    }

    public function removeHandledThematicCommunity(ThematicCommunity $thematicCommunity): void
    {
        $this->handledThematicCommunities->removeElement($thematicCommunity);
    }

    public function isThematicCommunityChief()
    {
        return $this->handledThematicCommunities->count() > 0;
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

    /**
     * @return NationalCouncilAdherentMandate[]
     */
    public function findNationalCouncilMandates(bool $active): array
    {
        return $this->adherentMandates->filter(function (AdherentMandateInterface $mandate) use ($active) {
            return $mandate instanceof NationalCouncilAdherentMandate
                && (false === $active || null === $mandate->getFinishAt());
        })->toArray();
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

    public function findTerritorialCouncilMandates(string $quality = null, bool $active = false): array
    {
        return $this->adherentMandates->filter(function (AdherentMandateInterface $mandate) use ($quality, $active) {
            return $mandate instanceof TerritorialCouncilAdherentMandate
                && (null === $quality || $mandate->getQuality() === $quality)
                && (false === $active || null === $mandate->getFinishAt());
        })->toArray();
    }

    public function getFilePermissions(): array
    {
        $roles = array_map(static function (string $role) {
            return str_replace('role_', '', mb_strtolower($role));
        }, $this->getRoles());

        return array_values(array_intersect(FilePermissionEnum::toArray(), $roles));
    }

    public function isNotifiedForElection(): bool
    {
        return $this->notifiedForElection;
    }

    public function notifyForElection(): void
    {
        $this->notifiedForElection = true;
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
    public function getSupervisorMandates(bool $isProvisional = null, string $gender = null): Collection
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

    public function isJemengageUser(): bool
    {
        return MembershipSourceEnum::JEMENGAGE === $this->source;
    }

    public function isRenaissanceUser(): bool
    {
        return MembershipSourceEnum::RENAISSANCE === $this->source;
    }

    public function isRenaissanceAdherent(): bool
    {
        return $this->isRenaissanceUser() && $this->hasTag(TagEnum::ADHERENT);
    }

    public function isRenaissanceSympathizer(): bool
    {
        return $this->isRenaissanceUser() && $this->hasTag(TagEnum::SYMPATHISANT);
    }

    public function addInstanceQuality($quality, \DateTime $data = null): AdherentInstanceQuality
    {
        if ($quality instanceof InstanceQuality) {
            if ($adherentInstanceQuality = $this->findInstanceQuality($quality)) {
                return $adherentInstanceQuality;
            }

            $quality = new AdherentInstanceQuality($this, $quality, $data ?? new \DateTime());
        }

        if (!$quality instanceof AdherentInstanceQuality) {
            throw new \InvalidArgumentException();
        }

        if (!$this->instanceQualities->contains($quality)) {
            $quality->setAdherent($this);
            $this->instanceQualities->add($quality);
        }

        return $quality;
    }

    public function removeInstanceQuality($quality): void
    {
        if ($quality instanceof InstanceQuality) {
            $quality = $this->findInstanceQuality($quality);
        }

        if ($quality) {
            $this->instanceQualities->removeElement($quality);
        }
    }

    public function hasNationalCouncilQualities(): bool
    {
        return 0 < $this->instanceQualities->filter(function (AdherentInstanceQuality $adherentQuality) {
            return $adherentQuality->hasNationalCouncilScope();
        })->count();
    }

    /**
     * @return AdherentInstanceQuality[]
     */
    public function getNationalCouncilQualities(): array
    {
        return $this->instanceQualities->filter(function (AdherentInstanceQuality $adherentQuality) {
            return $adherentQuality->hasNationalCouncilScope();
        })->toArray();
    }

    private function findInstanceQuality(InstanceQuality $quality): ?AdherentInstanceQuality
    {
        foreach ($this->instanceQualities as $adherentInstanceQuality) {
            if ($adherentInstanceQuality->getInstanceQuality()->equals($quality)) {
                return $adherentInstanceQuality;
            }
        }

        return null;
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
        $this->subscriptionTypes->clear();
    }

    /**
     * @Groups({"user_profile"})
     */
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

    public function setActivismZone(?Zone $zone): void
    {
        $this->activismZone = $zone;
    }

    public function getActivismZone(): ?Zone
    {
        return $this->activismZone;
    }

    public function donatedForMembership(\DateTimeInterface $donatedAt = null): void
    {
        if (!$donatedAt) {
            $donatedAt = new \DateTime('now');
        }

        if (!$this->lastMembershipDonation || $this->lastMembershipDonation < $donatedAt) {
            $this->lastMembershipDonation = $donatedAt;
        }
    }

    public function hasActiveMembership(): bool
    {
        return $this->isRenaissanceAdherent() && $this->hasTag(TagEnum::ADHERENT_COTISATION_OK);
    }

    public function getLastMembershipDonation(): ?\DateTimeInterface
    {
        return $this->lastMembershipDonation;
    }

    public function isExclusiveMembership(): bool
    {
        return $this->exclusiveMembership;
    }

    public function setExclusiveMembership(bool $exclusiveMembership): void
    {
        $this->exclusiveMembership = $exclusiveMembership;
    }

    public function isTerritoireProgresMembership(): bool
    {
        return $this->territoireProgresMembership;
    }

    public function setTerritoireProgresMembership(bool $territoireProgresMembership): void
    {
        $this->territoireProgresMembership = $territoireProgresMembership;
    }

    public function isAgirMembership(): bool
    {
        return $this->agirMembership;
    }

    public function setAgirMembership(bool $agirMembership): void
    {
        $this->agirMembership = $agirMembership;
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

    /**
     * @Groups({"adherent_elect_read"})
     */
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
        return $this->payments->filter(fn (Payment $p) => $p->isConfirmed())->toArray();
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

    /**
     * @Groups({"adherent_elect_read"})
     * @SerializedName("elect_mandates")
     */
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

    /**
     * @Groups({"adherent_elect_read"})
     */
    public function getLastRevenueDeclaration(): ?RevenueDeclaration
    {
        return $this->revenueDeclarations->first() ?: null;
    }

    public function hasTag(string $tag): bool
    {
        return \in_array($tag, $this->tags, true);
    }
}

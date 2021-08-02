<?php

namespace App\AdherentProfile;

use App\Address\Address;
use App\Entity\Adherent;
use App\Membership\MembershipInterface;
use App\Validator\AdherentInterests;
use App\Validator\UniqueMembership;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueMembership
 */
class AdherentProfile implements MembershipInterface
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="common.gender.not_blank")
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true,
     *     groups={"Default", "api_put_validation"}
     * )
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $gender;

    /**
     * @var string|null
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $customGender;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"Default", "api_put_validation"})
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     allowEmptyString=true,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length",
     *     groups={"Default", "api_put_validation"}
     * )
     *
     * @SymfonySerializer\Groups({"uncertified_profile_write"})
     */
    private $firstName;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=1,
     *     max=50,
     *     allowEmptyString=true,
     *     minMessage="common.last_name.min_length",
     *     maxMessage="common.last_name.max_length",
     *     groups={"Default", "api_put_validation"}
     * )
     *
     * @SymfonySerializer\Groups({"uncertified_profile_write"})
     */
    private $lastName;

    /**
     * @var Address
     *
     * @Assert\Valid
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $address;

    /**
     * @var string|null
     *
     * @Assert\Choice(
     *     callback={"App\Membership\ActivityPositions", "all"},
     *     message="adherent.activity_position.invalid_choice",
     *     strict=true,
     * )
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $position;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="adherent_profile.nationality.not_blank")
     * @Assert\Country(message="common.nationality.invalid", groups={"Default", "api_put_validation"})
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $nationality;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="adherent_profile.email.not_blank")
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $emailAddress;

    /**
     * @var PhoneNumber|null
     *
     * @AssertPhoneNumber(defaultRegion="FR")
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $phone;

    /**
     * @var \DateTime|null
     *
     * @Assert\NotBlank(message="adherent.birthdate.not_blank")
     * @Assert\Range(
     *     min="-120 years",
     *     max="-15 years",
     *     minMessage="adherent.birthdate.maximum_required_age",
     *     maxMessage="adherent.birthdate.minimum_required_age",
     *     groups={"Default", "api_put_validation"}
     * )
     *
     * @SymfonySerializer\Groups({"uncertified_profile_write"})
     */
    private $birthdate;

    /**
     * @var string
     *
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?facebook.com\/#", message="adherent_profile.facebook_page_url.invalid")
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $facebookPageUrl;

    /**
     * @var string
     *
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?twitter.com\/#", message="adherent_profile.twitter_page_url.invalid")
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $twitterPageUrl;

    /**
     * @var string
     *
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?linkedin.com\/#", message="adherent_profile.linkedin_page_url.invalid")
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $linkedinPageUrl;

    /**
     * @var string
     *
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?t.me\/#", message="adherent_profile.telegram_page_url.invalid")
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $telegramPageUrl;

    /**
     * @var string
     *
     * @Assert\Choice(
     *     choices=App\Entity\JobEnum::JOBS,
     *     message="adherent.job.invalid_choice",
     *     strict=true,
     * )
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $job;

    /**
     * @var string
     *
     * @Assert\Choice(
     *     choices=App\Entity\ActivityAreaEnum::ACTIVITIES,
     *     message="adherent.activity_area.invalid_choice",
     *     strict=true,
     * )
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $activityArea;

    /**
     * @var array
     *
     * @Assert\Choice(
     *     callback={"App\Membership\Mandates", "all"},
     *     multipleMessage="adherent_profile.mandates.invalid_choice",
     *     multiple=true
     * )
     */
    private $mandates = [];

    /**
     * @var array
     *
     * @SymfonySerializer\Groups({"profile_write"})
     *
     * @AdherentInterests
     */
    private $interests = [];

    /**
     * @var array
     *
     * @Assert\Choice(
     *     choices=App\Subscription\SubscriptionTypeEnum::ADHERENT_TYPES,
     *     multipleMessage="adherent_profile.subscription_types.invalid_choice",
     *     multiple=true,
     *     strict=true
     * )
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $subscriptionTypes = [];

    /**
     * @var bool
     *
     * @Assert\Type("bool", message="adherent_profile.coalition_subscription.invalid")
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $coalitionSubscription = false;

    /**
     * @var bool
     *
     * @Assert\Type("bool", message="adherent_profile.cause_subscription.invalid")
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $causeSubscription = false;

    /**
     * @var bool
     *
     * @Assert\Type("bool", message="adherent_profile.coalitions_cgu_accepted.invalid")
     *
     * @SymfonySerializer\Groups({"profile_write"})
     */
    private $coalitionsCguAccepted = false;

    public function __construct()
    {
        $this->address = new Address();
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        $dto = new self();
        $dto->customGender = $adherent->getCustomGender();
        $dto->gender = $adherent->getGender();
        $dto->firstName = $adherent->getFirstName();
        $dto->lastName = $adherent->getLastName();
        $dto->birthdate = $adherent->getBirthdate();
        $dto->position = $adherent->getPosition();
        $dto->address = Address::createFromAddress($adherent->getPostAddress());
        $dto->phone = $adherent->getPhone();
        $dto->emailAddress = $adherent->getEmailAddress();
        $dto->nationality = $adherent->getNationality();
        $dto->facebookPageUrl = $adherent->getFacebookPageUrl();
        $dto->twitterPageUrl = $adherent->getTwitterPageUrl();
        $dto->linkedinPageUrl = $adherent->getLinkedinPageUrl();
        $dto->telegramPageUrl = $adherent->getTelegramPageUrl();
        $dto->job = $adherent->getJob();
        $dto->activityArea = $adherent->getActivityArea();
        $dto->mandates = $adherent->getMandates();
        $dto->interests = $adherent->getInterests();
        $dto->subscriptionTypes = $adherent->getSubscriptionTypeCodes();
        $dto->coalitionSubscription = $adherent->isCoalitionSubscription();
        $dto->causeSubscription = $adherent->isCauseSubscription();
        $dto->coalitionsCguAccepted = $adherent->isCoalitionsCguAccepted();

        return $dto;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getCustomGender(): ?string
    {
        return $this->customGender;
    }

    public function setCustomGender(?string $customGender): void
    {
        $this->customGender = $customGender;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): void
    {
        $this->nationality = $nationality;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = mb_strtolower($emailAddress);
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress ?: '';
    }

    public function setPhone(?PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setBirthdate(?\DateTime $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): void
    {
        $this->position = $position;
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

    public function getMandates(): ?array
    {
        return $this->mandates;
    }

    public function setMandates(?array $mandates): void
    {
        $this->mandates = $mandates;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }

    public function setInterests(array $interests): void
    {
        $this->interests = $interests;
    }

    public function getSubscriptionTypes(): array
    {
        return $this->subscriptionTypes;
    }

    public function setSubscriptionTypes(array $subscriptionTypes): void
    {
        $this->subscriptionTypes = $subscriptionTypes;
    }

    public function isCoalitionSubscription(): bool
    {
        return $this->coalitionSubscription;
    }

    public function setCoalitionSubscription(bool $coalitionSubscription): void
    {
        $this->coalitionSubscription = $coalitionSubscription;
    }

    public function isCauseSubscription(): bool
    {
        return $this->causeSubscription;
    }

    public function setCauseSubscription(bool $causeSubscription): void
    {
        $this->causeSubscription = $causeSubscription;
    }

    public function isCoalitionsCguAccepted(): bool
    {
        return $this->coalitionsCguAccepted;
    }

    public function setCoalitionsCguAccepted(bool $coalitionsCguAccepted): void
    {
        $this->coalitionsCguAccepted = $coalitionsCguAccepted;
    }
}

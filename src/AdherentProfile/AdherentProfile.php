<?php

namespace App\AdherentProfile;

use App\Address\Address;
use App\Adherent\MandateTypeEnum;
use App\Entity\ActivityAreaEnum;
use App\Entity\Adherent;
use App\Entity\JobEnum;
use App\Membership\ActivityPositionsEnum;
use App\Membership\MembershipRequest\MembershipInterface;
use App\Renaissance\Membership\Admin\MembershipTypeEnum;
use App\Validator\AdherentInterests;
use App\Validator\UniqueMembership;
use App\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueMembership(groups: ['Default', 'api_email_change'])]
class AdherentProfile implements MembershipInterface
{
    /**
     * @var string|null
     */
    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.gender.invalid_choice', groups: ['Default', 'api_put_validation'])]
    #[Assert\NotBlank(message: 'common.gender.not_blank')]
    #[Groups(['profile_write'])]
    private $gender;

    /**
     * @var string|null
     */
    #[Groups(['profile_write'])]
    private $customGender;

    /**
     * @var string
     */
    #[Assert\Length(min: 2, max: 50, minMessage: 'common.first_name.min_length', maxMessage: 'common.first_name.max_length', groups: ['Default', 'api_put_validation'])]
    #[Assert\NotBlank(groups: ['Default', 'api_put_validation'])]
    #[Groups(['uncertified_profile_write'])]
    private $firstName;

    /**
     * @var string
     */
    #[Assert\Length(min: 1, max: 50, minMessage: 'common.last_name.min_length', maxMessage: 'common.last_name.max_length', groups: ['Default', 'api_put_validation'])]
    #[Assert\NotBlank(groups: ['Default', 'api_put_validation'])]
    #[Groups(['uncertified_profile_write'])]
    private $lastName;

    /**
     * @var Address
     */
    #[Assert\Valid]
    #[Groups(['profile_write'])]
    private $postAddress;

    /**
     * @var string|null
     */
    #[Assert\Choice(callback: [ActivityPositionsEnum::class, 'all'], message: 'adherent.activity_position.invalid_choice')]
    #[Groups(['profile_write'])]
    private $position;

    /**
     * @var string|null
     */
    #[Assert\Country(message: 'common.nationality.invalid', groups: ['Default', 'api_put_validation'])]
    #[Assert\Expression('value or !this.isAdherent', message: 'adherent_profile.nationality.not_blank')]
    #[Groups(['profile_write'])]
    private $nationality;

    /**
     * @var string
     */
    #[Assert\Email(message: 'common.email.invalid', groups: ['Default', 'api_put_validation', 'api_email_change'])]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length', groups: ['Default', 'api_put_validation', 'api_email_change'])]
    #[Assert\NotBlank(message: 'adherent_profile.email.not_blank', groups: ['Default', 'api_put_validation', 'api_email_change'])]
    #[Groups(['profile_write', 'profile_email_change'])]
    private $emailAddress;

    /**
     * @var PhoneNumber|null
     */
    #[AssertPhoneNumber]
    #[Groups(['profile_write'])]
    private $phone;

    /**
     * @var \DateTime|null
     */
    #[Assert\NotBlank(message: 'adherent.birthdate.not_blank')]
    #[Assert\Range(
        notInRangeMessage: 'common.birthdate.not_in_range',
        min: '-120 years',
        max: '-15 years',
        groups: ['Default', 'api_put_validation']
    )]
    #[Groups(['uncertified_profile_write', 'empty_profile_data'])]
    private $birthdate;

    /**
     * @var string
     */
    #[Assert\Regex(pattern: '#^https?\:\/\/(?:www\.)?facebook.com\/#', message: 'adherent_profile.facebook_page_url.invalid')]
    #[Groups(['profile_write'])]
    private $facebookPageUrl;

    /**
     * @var string
     */
    #[Assert\Regex(pattern: '#^https?\:\/\/(?:www\.)?twitter.com\/#', message: 'adherent_profile.twitter_page_url.invalid')]
    #[Groups(['profile_write'])]
    private $twitterPageUrl;

    /**
     * @var string
     */
    #[Assert\Regex(pattern: '#^https?\:\/\/(?:www\.)?linkedin.com\/#', message: 'adherent_profile.linkedin_page_url.invalid')]
    #[Groups(['profile_write'])]
    private $linkedinPageUrl;

    /**
     * @var string
     */
    #[Assert\Regex(pattern: '#^https?\:\/\/(?:www\.)?t.me\/#', message: 'adherent_profile.telegram_page_url.invalid')]
    #[Groups(['profile_write'])]
    private $telegramPageUrl;

    /**
     * @var string
     */
    #[Assert\Choice(choices: JobEnum::JOBS, message: 'adherent.job.invalid_choice')]
    #[Groups(['profile_write'])]
    private $job;

    /**
     * @var string
     */
    #[Assert\Choice(choices: ActivityAreaEnum::ACTIVITIES, message: 'adherent.activity_area.invalid_choice')]
    #[Groups(['profile_write'])]
    private $activityArea;

    /**
     * @var array
     */
    #[Assert\Choice(choices: MandateTypeEnum::ALL, multiple: true, multipleMessage: 'adherent_profile.mandates.invalid_choice')]
    #[Groups(['profile_write'])]
    private $mandates = [];

    /**
     * @var array
     */
    #[AdherentInterests]
    #[Groups(['profile_write'])]
    private $interests = [];

    /**
     * @var array
     */
    #[Groups(['profile_write'])]
    private $subscriptionTypes = [];

    #[Assert\Choice(choices: MembershipTypeEnum::CHOICES)]
    #[Groups(['profile_write'])]
    public ?string $partyMembership = null;

    // Used to skip nationality field validation when user is a sympathizer @see nationality field
    public bool $isAdherent = false;

    public function __construct()
    {
        $this->postAddress = new Address();
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
        $dto->postAddress = Address::createFromAddress($adherent->getPostAddress());
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
        $dto->isAdherent = $adherent->isRenaissanceAdherent();
        $dto->partyMembership = $adherent->partyMembership;

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

    public function setPostAddress(Address $postAddress): void
    {
        $this->postAddress = $postAddress;
    }

    public function getPostAddress(): Address
    {
        return $this->postAddress;
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

    public function getSource(): ?string
    {
        return null;
    }
}

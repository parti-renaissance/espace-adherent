<?php

namespace App\AdherentProfile;

use App\Address\Address;
use App\Entity\Adherent;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

class AdherentProfile
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="common.gender.not_blank")
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true,
     * )
     */
    private $gender;

    /**
     * @var string|null
     */
    private $customGender;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length",
     * )
     */
    private $firstName;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="common.last_name.min_length",
     *     maxMessage="common.last_name.max_length",
     * )
     */
    private $lastName;

    /**
     * @var Address
     *
     * @Assert\Valid
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
     */
    private $position;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Country(message="common.nationality.invalid")
     */
    private $nationality;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $emailAddress;

    /**
     * @var PhoneNumber|null
     *
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    private $phone;

    /**
     * @var \DateTime|null
     *
     * @Assert\NotBlank(message="adherent.birthdate.not_blank")
     * @Assert\Range(max="-15 years", maxMessage="adherent.birthdate.minimum_required_age")
     */
    private $birthdate;

    /**
     * @var string
     *
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?facebook.com\/#", message="adherent_profile.facebook_page_url.invalid")
     */
    private $facebookPageUrl;

    /**
     * @var string
     *
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?twitter.com\/#", message="adherent_profile.twitter_page_url.invalid")
     */
    private $twitterPageUrl;

    /**
     * @var string
     *
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?linkedin.com\/#", message="adherent_profile.linkedin_page_url.invalid")
     */
    private $linkedinPageUrl;

    /**
     * @var string
     *
     * @Assert\Regex(pattern="#^https?\:\/\/(?:www\.)?t.me\/#", message="adherent_profile.telegram_page_url.invalid")
     */
    private $telegramPageUrl;

    /**
     * @var string
     */
    private $job;

    /**
     * @var string
     */
    private $activityArea;

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

    public function setFacebookPageUrl(string $facebookPageUrl): void
    {
        $this->facebookPageUrl = $facebookPageUrl;
    }

    public function getTwitterPageUrl(): ?string
    {
        return $this->twitterPageUrl;
    }

    public function setTwitterPageUrl(string $twitterPageUrl): void
    {
        $this->twitterPageUrl = $twitterPageUrl;
    }

    public function getLinkedinPageUrl(): ?string
    {
        return $this->linkedinPageUrl;
    }

    public function setLinkedinPageUrl(string $linkedinPageUrl): void
    {
        $this->linkedinPageUrl = $linkedinPageUrl;
    }

    public function getTelegramPageUrl(): ?string
    {
        return $this->telegramPageUrl;
    }

    public function setTelegramPageUrl(string $telegramPageUrl): void
    {
        $this->telegramPageUrl = $telegramPageUrl;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(string $job): void
    {
        $this->job = $job;
    }

    public function getActivityArea(): ?string
    {
        return $this->activityArea;
    }

    public function setActivityArea(string $activityArea): void
    {
        $this->activityArea = $activityArea;
    }
}

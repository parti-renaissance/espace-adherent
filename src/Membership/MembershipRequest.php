<?php

namespace AppBundle\Membership;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Validator\BannedAdherent;
use AppBundle\Validator\Recaptcha as AssertRecaptcha;
use AppBundle\Validator\UniqueMembership as AssertUniqueMembership;
use AppBundle\Validator\CustomGender as AssertCustomGender;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueMembership(groups={"Registration", "Update"})
 * @AssertCustomGender(groups={"Registration", "Update"})
 */
class MembershipRequest implements MembershipInterface
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="common.gender.not_blank", groups={"Update"})
     * @Assert\Choice(
     *     callback={"AppBundle\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true,
     *     groups={"Update"}
     * )
     */
    public $gender;

    /**
     * @var string|null
     */
    public $customGender;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"Registration", "Update"})
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length",
     *     groups={"Registration", "Update"}
     * )
     */
    public $firstName;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"Registration", "Update"})
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="common.last_name.min_length",
     *     maxMessage="common.last_name.max_length",
     *     groups={"Registration", "Update"}
     * )
     */
    public $lastName;

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
     *     callback={"AppBundle\Membership\ActivityPositions", "all"},
     *     message="adherent.activity_position.invalid_choice",
     *     strict=true,
     *     groups={"Update"}
     * )
     */
    public $position;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups="Registration")
     * @Assert\Length(min=8, minMessage="adherent.plain_password.min_length", groups={"Registration"})
     */
    public $password;

    /**
     * @var bool
     *
     * @Assert\IsTrue(message="common.conditions.not_accepted", groups={"Conditions"})
     */
    public $conditions;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(groups={"Registration", "Update"})
     * @Assert\Country(message="common.nationality.invalid")
     */
    public $nationality;

    private $allowNotifications = false;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="common.recaptcha.invalid_message", groups={"Registration"})
     * @AssertRecaptcha(groups={"Registration"})
     */
    public $recaptcha;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"Registration", "Update"})
     * @Assert\Email(message="common.email.invalid", groups={"Registration", "Update"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"Registration", "Update"})
     * @BannedAdherent(groups={"Registration"})
     */
    private $emailAddress;

    /**
     * @var PhoneNumber|null
     *
     * @AssertPhoneNumber(defaultRegion="FR", groups={"Update"})
     */
    private $phone;

    /**
     * @var \DateTime|null
     *
     * @Assert\NotBlank(message="adherent.birthdate.not_blank", groups={"Update"})
     * @Assert\Range(max="-15 years", maxMessage="adherent.birthdate.minimum_required_age", groups={"Update"})
     */
    private $birthdate;

    /**
     * @var bool
     */
    private $elected = false;

    /**
     * @var array
     *
     * @Assert\Choice(
     *     callback={"AppBundle\Membership\Mandates", "all"}
     * )
     */
    private $mandates;

    public function __construct()
    {
        $this->address = new Address();
    }

    public static function createWithCaptcha(?string $countryIso, string $recaptchaAnswer = null): self
    {
        $dto = new self();
        $dto->recaptcha = $recaptchaAnswer;

        if ($countryIso) {
            $dto->address->setCountry($countryIso);
        }

        return $dto;
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
        $dto->mandates = $adherent->getMandates();
        $dto->elected = $adherent->hasMandate();
        $dto->nationality = $adherent->getNationality();

        return $dto;
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

    public function getAllowNotifications(): bool
    {
        return $this->allowNotifications;
    }

    public function setAllowNotifications(bool $allowNotifications): void
    {
        $this->allowNotifications = $allowNotifications;
    }

    public function isElected(): bool
    {
        return $this->elected;
    }

    public function setElected(bool $elected): void
    {
        $this->elected = $elected;
    }

    public function getMandates(): ?array
    {
        return $this->mandates;
    }

    public function setMandates(?array $mandates): void
    {
        $this->mandates = $mandates;
    }
}

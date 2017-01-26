<?php

namespace AppBundle\Membership;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Validator\Recaptcha as AssertRecaptcha;
use AppBundle\Validator\UniqueMembership as AssertUniqueMembership;
use AppBundle\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueMembership
 */
class MembershipRequest
{
    /**
     * @Assert\Choice(
     *   callback = {"AppBundle\ValueObject\Genders", "all"},
     *   message="common.gender.invalid_choice",
     *   strict=true
     * )
     */
    public $gender;

    /**
     * @Assert\NotBlank(message="common.first_name.not_blank")
     * @Assert\Length(
     *   min=2,
     *   max=50,
     *   minMessage="common.first_name.min_length",
     *   maxMessage="common.first_name.max_length"
     * )
     */
    public $firstName;

    /**
     * @Assert\NotBlank(message="common.first_name.not_blank")
     * @Assert\Length(
     *   min=2,
     *   max=50,
     *   minMessage="common.last_name.min_length",
     *   maxMessage="common.last_name.max_length"
     * )
     */
    public $lastName;

    /**
     * @Assert\Valid
     *
     * @var Address
     */
    private $address;

    /**
     * @Assert\Choice(
     *   callback = {"AppBundle\Membership\ActivityPositions", "all"},
     *   message="adherent.activity_position.invalid_choice",
     *   strict=true
     * )
     */
    public $position;

    /**
     * @Assert\NotBlank(groups="Registration")
     * @Assert\Length(min=8, minMessage="adherent.plain_password.min_length", groups={"Registration"})
     */
    public $password;

    /**
     * @Assert\IsTrue(message="common.conditions.not_accepted", groups={"Registration"})
     */
    public $conditions;

    /**
     * @AssertRecaptcha(groups={"Registration"})
     */
    public $recaptcha;

    /**
     * @Assert\NotBlank(message="common.email.not_blank", groups={"Registration"})
     * @Assert\Email(message="common.email.invalid", groups={"Registration"})
     */
    private $emailAddress;

    /**
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    private $phone;

    /**
     * @var Adherent
     */
    private $adherent;

    /**
     * @Assert\NotBlank(message="adherent.birthdate.not_blank")
     * @Assert\Range(max="-15 years", maxMessage="adherent.birthdate.minimum_required_age")
     */
    private $birthdate;

    public function __construct()
    {
        $this->gender = Genders::MALE;
        $this->position = ActivityPositions::EMPLOYED;
        $this->conditions = false;
        $this->emailAddress = '';
        $this->address = new Address();
    }

    public static function createWithCaptcha(string $recaptchaAnswer = null): self
    {
        $dto = new self();
        $dto->recaptcha = $recaptchaAnswer;
        $dto->phone = static::createPhoneNumber();

        return $dto;
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        $dto = new self();
        $dto->gender = $adherent->getGender();
        $dto->firstName = $adherent->getFirstName();
        $dto->lastName = $adherent->getLastName();
        $dto->birthdate = $adherent->getBirthdate();
        $dto->position = $adherent->getPosition();
        $dto->address = Address::createFromPostAddress($adherent);
        $dto->phone = $adherent->getPhone();

        return $dto;
    }

    private static function createPhoneNumber(int $countryCode = 33, string $number = null)
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode($countryCode);

        if ($number) {
            $phone->setNationalNumber($number);
        }

        return $phone;
    }

    /**
     * Sets an Address instance.
     *
     * @param Address|null $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Returns an Address instance.
     *
     * @return Address|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    public function setEmailAddress(string $emailAddress)
    {
        $this->emailAddress = mb_strtolower($emailAddress);
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function setPhone(PhoneNumber $phone = null)
    {
        $this->phone = $phone;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setBirthdate(\DateTime $birthdate = null)
    {
        $this->birthdate = $birthdate;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * @return Adherent|null
     */
    public function getAdherent()
    {
        return $this->adherent;
    }

    /**
     * @param Adherent|null $adherent
     */
    public function setAdherent(Adherent $adherent)
    {
        $this->adherent = $adherent;
    }

    public function hasAdherent(): bool
    {
        return null !== $this->adherent;
    }
}

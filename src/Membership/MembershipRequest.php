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
class MembershipRequest implements MembershipInterface
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
     * @Assert\IsTrue(message="common.conditions.not_accepted", groups={"Registration"})
     */
    public $conditions;

    public $comMobile = false;

    public $comEmail = false;

    /**
     * @Assert\NotBlank(message="common.recaptcha.invalid_message", groups="Registration")
     * @AssertRecaptcha(groups={"Registration"})
     */
    public $recaptcha;

    /**
     * @Assert\NotBlank(message="common.email.not_blank")
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $emailAddress;

    /**
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    private $phone;

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
        $dto->address = Address::createFromAddress($adherent->getPostAddress());
        $dto->phone = $adherent->getPhone();
        $dto->comMobile = $adherent->getComMobile();
        $dto->comEmail = $adherent->getComEmail();
        $dto->emailAddress = $adherent->getEmailAddress();

        return $dto;
    }

    private static function createPhoneNumber(int $countryCode = 33, string $number = null): PhoneNumber
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode($countryCode);

        if ($number) {
            $phone->setNationalNumber($number);
        }

        return $phone;
    }

    public function setAddress(Address $address = null): void
    {
        $this->address = $address;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = mb_strtolower($emailAddress);
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setPhone(PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setBirthdate(\DateTime $birthdate = null): void
    {
        $this->birthdate = $birthdate;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }
}

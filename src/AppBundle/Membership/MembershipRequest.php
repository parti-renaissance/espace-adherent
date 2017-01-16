<?php

namespace AppBundle\Membership;

use AppBundle\Validator\CityAssociatedToPostalCode as AssertCityAssociatedToPostalCode;
use AppBundle\Validator\FrenchCity as AssertFrenchCity;
use AppBundle\Validator\FrenchPostalCode as AssertFrenchPostalCode;
use AppBundle\Validator\Recaptcha as AssertRecaptcha;
use AppBundle\Validator\UniqueMembership as AssertUniqueMembership;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use AppBundle\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertCityAssociatedToPostalCode(postalCodeField="postalCode", cityField="city", message="common.city.invalid_postal_code")
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
     * @Assert\NotBlank(message="common.email.not_blank")
     * @Assert\Email(message="common.email.invalid")
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

    /**
     * @Assert\Length(max=150, maxMessage="common.address.max_length")
     */
    public $address;

    /**
     * @AssertFrenchPostalCode(message="common.postal_code.invalid")
     */
    public $postalCode;

    /**
     * @AssertFrenchCity(message="common.city.invalid")
     */
    public $city;

    /**
     * @AssertUnitedNationsCountry(message="common.country.invalid")
     */
    public $country;

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
     * @Assert\Length(min=8, minMessage="adherent.plain_password.min_length")
     */
    public $password;

    /**
     * @Assert\IsTrue(message="common.conditions.not_accepted")
     */
    public $conditions;

    /**
     * @AssertRecaptcha
     */
    public $recaptcha;

    public function __construct()
    {
        $this->country = 'FR';
        $this->gender = Genders::MALE;
        $this->position = ActivityPositions::EMPLOYED;
        $this->conditions = false;
    }

    public static function createWithCaptcha(string $recaptchaAnswer = null): self
    {
        $dto = new self();
        $dto->recaptcha = $recaptchaAnswer;
        $dto->phone = static::createPhoneNumber();

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

    public function setBirthdate(\DateTime $birthdate)
    {
        $this->birthdate = $birthdate;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }
}

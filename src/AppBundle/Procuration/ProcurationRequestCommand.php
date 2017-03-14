<?php

namespace AppBundle\Procuration;

use AppBundle\Entity\Adherent;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProcurationRequestCommand
{
    /**
     * @Assert\NotBlank(groups={"vote"})
     * @Assert\Length(max=15, groups={"vote"})
     */
    public $votePostalCode;

    /**
     * @Assert\Length(max=15, groups={"vote"})
     */
    public $voteCity;

    /**
     * @Assert\Length(max=255, groups={"vote"})
     */
    public $voteCityName;

    /**
     * @Assert\NotBlank(groups={"vote"})
     * @AssertUnitedNationsCountry(message="common.country.invalid", groups={"vote"})
     */
    public $voteCountry;

    /**
     * @Assert\NotBlank(message="common.address.required", groups={"address"})
     * @Assert\Length(max=150, maxMessage="common.address.max_length", groups={"address"})
     */
    public $address;

    /**
     * @Assert\NotBlank(groups={"address"})
     * @Assert\Length(max=15, groups={"address"})
     */
    public $postalCode;

    /**
     * @Assert\Length(max=15, groups={"address"})
     */
    public $city;

    /**
     * @Assert\Length(max=255, groups={"address"})
     */
    public $cityName;

    /**
     * @Assert\NotBlank(groups={"address"})
     * @AssertUnitedNationsCountry(message="common.country.invalid", groups={"address"})
     */
    public $country;

    /**
     * @Assert\NotBlank(message="common.gender.invalid_choice", groups={"profile"})
     * @Assert\Choice(
     *      callback = {"AppBundle\ValueObject\Genders", "all"},
     *      message="common.gender.invalid_choice",
     *      strict=true,
     *      groups={"profile"}
     * )
     */
    public $gender;

    /**
     * @Assert\NotBlank(message="common.first_name.not_blank", groups={"profile"})
     * @Assert\Length(
     *      min=2,
     *      max=50,
     *      minMessage="common.first_name.min_length",
     *      maxMessage="common.first_name.max_length",
     *      groups={"profile"}
     * )
     */
    public $firstName;

    /**
     * @Assert\NotBlank(message="common.first_name.not_blank", groups={"profile"})
     * @Assert\Length(
     *      min=2,
     *      max=50,
     *      minMessage="common.last_name.min_length",
     *      maxMessage="common.last_name.max_length",
     *      groups={"profile"}
     * )
     */
    public $lastName;

    /**
     * @Assert\NotBlank(message="common.email.not_blank", groups={"profile"})
     * @Assert\Email(message="common.email.invalid", groups={"profile"})
     */
    public $emailAddress;

    /**
     * @AssertPhoneNumber(defaultRegion="FR", groups={"profile"})
     */
    public $phone;

    public $electionPresidentialFirstRound;
    public $electionPresidentialSecondRound;
    public $electionLegislativeFirstRound;
    public $electionLegislativeSecondRound;

    public function __construct()
    {
        $this->emailAddress = '';
        $this->country = 'FR';
        $this->voteCountry = 'FR';
        $this->phone = static::createPhoneNumber();
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
     * @Assert\Callback(groups={"elections"})
     *
     * @param ExecutionContextInterface $context
     */
    public function validateElectionsChosen(ExecutionContextInterface $context)
    {
        if ($this->electionPresidentialFirstRound) {
            return;
        }

        if ($this->electionPresidentialSecondRound) {
            return;
        }

        if ($this->electionLegislativeFirstRound) {
            return;
        }

        if ($this->electionLegislativeSecondRound) {
            return;
        }

        $context->addViolation('Vous devez choisir au moins une élection');
    }
}

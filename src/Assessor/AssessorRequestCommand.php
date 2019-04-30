<?php

namespace AppBundle\Assessor;

use AppBundle\Entity\AssessorOfficeEnum;
use AppBundle\Validator\Recaptcha as AssertRecaptcha;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use AppBundle\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

class AssessorRequestCommand
{
    /**
     * @Assert\NotBlank(message="common.gender.invalid_choice", groups={"fill_personal_info"})
     * @Assert\Choice(
     *     callback={"AppBundle\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true,
     *     groups={"fill_personal_info"}
     * )
     */
    private $gender;

    /**
     * @Assert\NotBlank(message="assessor.last_name.not_blank", groups={"fill_personal_info"})
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="assessor.last_name.min_length",
     *     maxMessage="assessor.last_name.max_length",
     *     groups={"fill_personal_info"}
     * )
     */
    private $lastName;

    /**
     * @Assert\NotBlank(message="assessor.first_name.not_blank", groups={"fill_personal_info"})
     * @Assert\Length(
     *     min=2,
     *     max=100,
     *     minMessage="assessor.first_name.min_length",
     *     maxMessage="assessor.first_name.max_length",
     *     groups={"fill_personal_info"}
     * )
     */
    private $firstName;

    /**
     * @Assert\NotBlank(message="common.birth_name.not_blank", groups={"fill_personal_info"})
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="common.birth_name.min_length",
     *     maxMessage="common.birth_name.max_length",
     *     groups={"fill_personal_info"}
     * )
     */
    private $birthName;

    /**
     * @Assert\NotBlank(message="common.birthdate.not_blank", groups={"fill_personal_info"})
     * @Assert\Range(
     *     max="-18 years",
     *     maxMessage="assessor.birthdate.minimum_required_age",
     *     groups={"fill_personal_info"}
     * )
     */
    private $birthdate;

    /**
     * @Assert\NotBlank(message="common.birthcity.not_blank", groups={"fill_personal_info"})
     * @Assert\Length(max=15, groups={"fill_personal_info"})
     */
    private $birthCity;

    /**
     * @Assert\NotBlank(message="common.address.required", groups={"fill_personal_info"})
     * @Assert\Length(max=150, maxMessage="common.address.max_length", groups={"fill_personal_info"})
     */
    private $address;

    /**
     * @Assert\NotBlank(message="common.postal_code.not_blank", groups={"fill_personal_info"})
     * @Assert\Length(max=15, groups={"fill_personal_info"})
     */
    private $postalCode;

    /**
     * @Assert\NotBlank(message="common.city_name.not_blank", groups={"fill_personal_info"})
     * @Assert\Length(max=15, groups={"fill_personal_info"})
     */
    private $city;

    /**
     * @Assert\NotBlank(message="assessor.vote_city.not_blank", groups={"fill_personal_info"})
     * @Assert\Length(max=15, groups={"fill_personal_info"})
     */
    private $voteCity;

    /**
     * @Assert\NotBlank(message="assessor.office_number.not_blank", groups={"fill_personal_info"})
     * @Assert\Length(max=10, groups={"fill_personal_info"})
     */
    private $officeNumber;

    /**
     * @Assert\NotBlank(groups={"fill_personal_info"})
     * @Assert\Email(message="common.email.invalid", groups={"fill_personal_info"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"fill_personal_info"})
     */
    private $emailAddress;

    /**
     * @Assert\NotBlank(message="common.phone_number.required", groups={"fill_personal_info"})
     * @AssertPhoneNumber(defaultRegion="FR", groups={"fill_personal_info"})
     */
    private $phone;

    /**
     * @Assert\Expression(
     *     "(this.isFrenchAssessorRequest() and value != null) or (!this.isFrenchAssessorRequest() and value == null)",
     *     message="assessor.assessor_city.not_blank",
     *     groups={"fill_assessor_info"}
     * )
     * @Assert\Length(max=255, groups={"fill_assessor_info"})
     */
    private $assessorCity;

    /**
     * @Assert\Expression(
     *     "(this.isFrenchAssessorRequest() and value != null) or (!this.isFrenchAssessorRequest() and value == null)",
     *     message="assessor.assessor_postal_code.not_blank",
     *     groups={"fill_assessor_info"}
     * )
     * @Assert\Length(max=15, groups={"fill_assessor_info"})
     */
    private $assessorPostalCode;

    /**
     * @Assert\NotBlank(groups={"fill_assessor_info"})
     * @AssertUnitedNationsCountry(message="common.country.invalid", groups={"fill_assessor_info"})
     */
    private $assessorCountry = 'FR';

    /**
     * @Assert\NotBlank(message="assessor.office.invalid_choice", groups={"fill_assessor_info"})
     * @Assert\Choice(
     *     callback={"AppBundle\Entity\AssessorOfficeEnum", "toArray"},
     *     message="assessor.office.invalid_choice",
     *     strict=true,
     *     groups={"fill_assessor_info"}
     * )
     */
    private $office = AssessorOfficeEnum::HOLDER;

    /**
     * @Assert\NotBlank(message="assessor.vote_place_wishes.not_blank", groups={"fill_assessor_info"})
     */
    private $votePlaceWishes = [];

    /**
     * @Assert\NotBlank(message="common.recaptcha.invalid_message", groups={"valid_summary"})
     * @AssertRecaptcha(groups={"valid_summary"})
     */
    public $recaptcha = '';

    public $reachable = false;

    /**
     * Handled by the workflow.
     *
     * @var string
     */
    public $marking;

    public function __construct()
    {
        $this->phone = static::createPhoneNumber();
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getBirthName(): ?string
    {
        return $this->birthName;
    }

    public function setBirthName(string $birthName): void
    {
        $this->birthName = $birthName;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTime $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function getBirthCity(): ?string
    {
        return $this->birthCity;
    }

    public function setBirthCity(?string $birthCity): void
    {
        $this->birthCity = $birthCity;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getVoteCity(): ?string
    {
        return $this->voteCity;
    }

    public function setVoteCity(string $voteCity): void
    {
        $this->voteCity = $voteCity;
    }

    public function getOfficeNumber(): ?string
    {
        return $this->officeNumber;
    }

    public function setOfficeNumber(string $officeNumber): void
    {
        $this->officeNumber = $officeNumber;
    }

    public function getEmailAddress(): ?string
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

    public function setPhone(PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    public function getAssessorCity(): ?string
    {
        return $this->assessorCity;
    }

    public function setAssessorCity(?string $assessorCity): void
    {
        $this->assessorCity = $assessorCity;
    }

    public function getAssessorPostalCode(): ?string
    {
        return $this->assessorPostalCode;
    }

    public function setAssessorPostalCode(?string $assessorPostalCode): void
    {
        $this->assessorPostalCode = $assessorPostalCode;
    }

    public function getAssessorCountry(): ?string
    {
        return $this->assessorCountry;
    }

    public function setAssessorCountry(string $assessorCountry): void
    {
        $this->assessorCountry = $assessorCountry;
    }

    public function getOffice(): ?string
    {
        return $this->office;
    }

    public function setOffice(?string $office): void
    {
        $this->office = $office;
    }

    public function getRecaptcha(): string
    {
        return $this->recaptcha;
    }

    public function setRecaptcha(string $recaptcha): void
    {
        $this->recaptcha = $recaptcha;
    }

    public function getVotePlaceWishes(): array
    {
        return $this->votePlaceWishes;
    }

    public function setVotePlaceWishes(array $votePlaceWishes): void
    {
        $this->votePlaceWishes = $votePlaceWishes;
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

    public function getGenderName(): ?string
    {
        return array_search($this->gender, Genders::CHOICES);
    }

    public function getOfficeName(): ?string
    {
        return array_search($this->office, AssessorOfficeEnum::CHOICES);
    }

    public function isFrenchAssessorRequest(): bool
    {
        return 'FR' === $this->getAssessorCountry();
    }

    public function isReachable(): bool
    {
        return $this->reachable;
    }

    public function setReachable(bool $reachable): void
    {
        $this->reachable = $reachable;
    }
}

<?php

namespace AppBundle\Entity;

use AppBundle\Intl\FranceCitiesBundle;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Utils\EmojisRemover;
use AppBundle\Validator\Recaptcha as AssertRecaptcha;

/**
 * @ORM\Table(name="procuration_requests")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcurationRequestRepository")
 */
class ProcurationRequest
{
    const REASON_PROFESIONNAL = 'profesionnal';
    const REASON_HANDICAP = 'handicap';
    const REASON_HEALTH = 'health';
    const REASON_HELP = 'help';
    const REASON_TRAINING = 'training';
    const REASON_HOLIDAYS = 'holidays';
    const REASON_RESIDENCY = 'residency';

    use EntityTimestampableTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6)
     *
     * @Assert\NotBlank(message="common.gender.invalid_choice", groups={"profile"})
     * @Assert\Choice(
     *      callback={"AppBundle\ValueObject\Genders", "all"},
     *      message="common.gender.invalid_choice",
     *      strict=true,
     *      groups={"profile"}
     * )
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="procuration.last_name.not_blank", groups={"profile"})
     * @Assert\Length(
     *      min=2,
     *      max=50,
     *      minMessage="procuration.last_name.min_length",
     *      maxMessage="procuration.last_name.max_length",
     *      groups={"profile"}
     * )
     */
    private $lastName = '';

    /**
     * @var string
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank(message="procuration.first_names.not_blank", groups={"profile"})
     * @Assert\Length(
     *      min=2,
     *      max=100,
     *      minMessage="procuration.first_names.min_length",
     *      maxMessage="procuration.first_names.max_length",
     *      groups={"profile"}
     * )
     */
    private $firstNames = '';

    /**
     * @var string
     *
     * @ORM\Column(length=150)
     *
     * @Assert\NotBlank(message="common.address.required", groups={"profile"})
     * @Assert\Length(max=150, maxMessage="common.address.max_length", groups={"profile"})
     */
    private $address = '';

    /**
     * @var string
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Length(max=15, groups={"profile"})
     */
    private $postalCode = '';

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true, name="city_insee")
     *
     * @Assert\Length(max=15, groups={"profile"})
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255, groups={"profile"})
     */
    private $cityName;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank(groups={"profile"})
     * @AssertUnitedNationsCountry(message="common.country.invalid", groups={"profile"})
     */
    private $country = 'FR';

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber(defaultRegion="FR", groups={"profile"})
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(message="common.email.not_blank", groups={"profile"})
     * @Assert\Email(message="common.email.invalid", groups={"profile"})
     */
    private $emailAddress = '';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Assert\NotBlank(message="procuration.birthdate.not_blank", groups={"profile"})
     * @Assert\Range(max="-17 years", maxMessage="procuration.birthdate.minimum_required_age", groups={"profile"})
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Length(max=15, groups={"vote"})
     */
    private $votePostalCode = '';

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true, name="vote_city_insee")
     *
     * @Assert\Length(max=15, groups={"vote"})
     */
    private $voteCity;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255, groups={"vote"})
     */
    private $voteCityName;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank(groups={"vote"})
     * @AssertUnitedNationsCountry(message="common.country.invalid", groups={"vote"})
     */
    private $voteCountry = 'FR';

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     *
     * @Assert\Length(max=50, groups={"vote"})
     */
    private $voteOffice;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $electionPresidentialFirstRound = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $electionPresidentialSecondRound = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $electionLegislativeFirstRound = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $electionLegislativeSecondRound = true;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     *
     * @Assert\NotBlank(message="common.gender.invalid_choice", groups={"elections"})
     * @Assert\Choice(
     *      callback={"AppBundle\Entity\ProcurationRequest", "getReasons"},
     *      message="common.gender.invalid_choice",
     *      strict=true,
     *      groups={"profile"}
     * )
     */
    private $reason = self::REASON_RESIDENCY;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="common.recaptcha.invalid_message", groups={"elections"})
     * @AssertRecaptcha(groups={"elections"})
     */
    public $recaptcha = '';

    public function __construct()
    {
        $this->phone = static::createPhoneNumber();
    }

    public function __toString()
    {
        return 'Demande de procuration de '.$this->lastName.' '.$this->firstNames;
    }

    public static function getReasons(): array
    {
        return [
            self::REASON_HANDICAP,
            self::REASON_HEALTH,
            self::REASON_HELP,
            self::REASON_HOLIDAYS,
            self::REASON_PROFESIONNAL,
            self::REASON_RESIDENCY,
            self::REASON_TRAINING,
        ];
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

    public function importAdherentData(Adherent $adherent)
    {
        $this->gender = $adherent->getGender();
        $this->setFirstNames($adherent->getFirstName());
        $this->setLastName($adherent->getLastName());
        $this->emailAddress = $adherent->getEmailAddress();
        $this->setAddress($adherent->getAddress());
        $this->postalCode = $adherent->getPostalCode();
        $this->setCity($adherent->getCity());
        $this->setCityName($adherent->getCityName());
        $this->country = $adherent->getCountry();

        if ($adherent->getPhone()) {
            $this->phone = $adherent->getPhone();
        }

        if ($adherent->getBirthdate()) {
            $this->birthdate = $adherent->getBirthdate();
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName)
    {
        $this->lastName = EmojisRemover::remove($lastName);
    }

    public function getFirstNames(): ?string
    {
        return $this->firstNames;
    }

    public function setFirstNames(?string $firstNames)
    {
        $this->firstNames = EmojisRemover::remove($firstNames);
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address)
    {
        $this->address = EmojisRemover::remove($address);
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity(?string $cityCode)
    {
        $this->city = $cityCode;

        if ($cityCode && false !== strpos($cityCode, '-')) {
            list($postalCode, $inseeCode) = explode('-', $cityCode);
            $this->cityName = (string) FranceCitiesBundle::getCity($postalCode, $inseeCode);
        }
    }

    public function getCityName()
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName)
    {
        if ($cityName) {
            $this->cityName = EmojisRemover::remove($cityName);
        }
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country)
    {
        $this->country = $country;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    }

    public function getVotePostalCode(): ?string
    {
        return $this->votePostalCode;
    }

    public function setVotePostalCode(?string $votePostalCode)
    {
        $this->votePostalCode = $votePostalCode;
    }

    public function getVoteCity()
    {
        return $this->voteCity;
    }

    public function setVoteCity(?string $cityCode)
    {
        $this->voteCity = $cityCode;

        if ($cityCode && false !== strpos($cityCode, '-')) {
            list($postalCode, $inseeCode) = explode('-', $cityCode);
            $this->voteCityName = (string) FranceCitiesBundle::getCity($postalCode, $inseeCode);
        }
    }

    public function getVoteCityName()
    {
        return $this->voteCityName;
    }

    public function setVoteCityName(?string $voteCityName)
    {
        if ($voteCityName) {
            $this->voteCityName = EmojisRemover::remove($voteCityName);
        }
    }

    public function getVoteCountry(): ?string
    {
        return $this->voteCountry;
    }

    public function setVoteCountry(?string $voteCountry)
    {
        $this->voteCountry = $voteCountry;
    }

    public function getVoteOffice(): ?string
    {
        return $this->voteOffice;
    }

    public function setVoteOffice(?string $voteOffice)
    {
        $this->voteOffice = $voteOffice;
    }

    public function getElectionPresidentialFirstRound(): bool
    {
        return $this->electionPresidentialFirstRound;
    }

    public function setElectionPresidentialFirstRound(bool $electionPresidentialFirstRound)
    {
        $this->electionPresidentialFirstRound = $electionPresidentialFirstRound;
    }

    public function getElectionPresidentialSecondRound(): bool
    {
        return $this->electionPresidentialSecondRound;
    }

    public function setElectionPresidentialSecondRound(bool $electionPresidentialSecondRound)
    {
        $this->electionPresidentialSecondRound = $electionPresidentialSecondRound;
    }

    public function getElectionLegislativeFirstRound(): bool
    {
        return $this->electionLegislativeFirstRound;
    }

    public function setElectionLegislativeFirstRound(bool $electionLegislativeFirstRound)
    {
        $this->electionLegislativeFirstRound = $electionLegislativeFirstRound;
    }

    public function getElectionLegislativeSecondRound(): bool
    {
        return $this->electionLegislativeSecondRound;
    }

    public function setElectionLegislativeSecondRound(bool $electionLegislativeSecondRound)
    {
        $this->electionLegislativeSecondRound = $electionLegislativeSecondRound;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason)
    {
        $this->reason = $reason;
    }
}

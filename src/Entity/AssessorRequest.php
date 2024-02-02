<?php

namespace App\Entity;

use App\Address\AddressInterface;
use App\Assessor\AssessorRequestElectionRoundsEnum;
use App\Entity\Election\VotePlace as ElectionVotePlace;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\ValueObject\Genders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="assessor_requests")
 * @ORM\Entity(repositoryClass="App\Repository\AssessorRequestRepository")
 *
 * @AssertRecaptcha
 */
class AssessorRequest implements RecaptchaChallengeInterface
{
    use EntityTimestampableTrait;
    use EntityIdentityTrait;
    use RecaptchaChallengeTrait;

    /**
     * @var string
     *
     * @ORM\Column(length=6)
     *
     * @Assert\NotBlank(message="common.gender.invalid_choice")
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice"
     * )
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="assessor.last_name.not_blank")
     * @Assert\Length(
     *     min=1,
     *     max=50,
     *     minMessage="assessor.last_name.min_length",
     *     maxMessage="assessor.last_name.max_length"
     * )
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank(message="assessor.first_name.not_blank")
     * @Assert\Length(
     *     min=2,
     *     max=100,
     *     minMessage="assessor.first_name.min_length",
     *     maxMessage="assessor.first_name.max_length"
     * )
     */
    private $firstName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank(message="common.birthdate.not_blank")
     * @Assert\Range(
     *     min="-120 years",
     *     max="-18 years",
     *     minMessage="assessor.birthdate.maximum_required_age",
     *     maxMessage="assessor.birthdate.minimum_required_age"
     * )
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="common.birthcity.not_blank")
     * @Assert\Length(max=50)
     */
    private $birthCity;

    /**
     * @var string
     *
     * @ORM\Column(length=150)
     *
     * @Assert\NotBlank(message="common.address.required")
     * @Assert\Length(max=150, maxMessage="common.address.max_length")
     */
    private $address;

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Length(max=15)
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="common.city_name.not_blank")
     * @Assert\Length(max=50)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(length=2, options={"default": "FR"})
     *
     * @Assert\NotBlank(message="common.country.not_blank")
     * @Assert\Country(message="common.country.invalid")
     */
    private $country = AddressInterface::FRANCE;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="assessor.vote_city.not_blank")
     * @Assert\Length(max=50)
     */
    private $voteCity;

    /**
     * @var string|null
     *
     * @ORM\Column(length=10, nullable=true)
     *
     * @Assert\Length(max=10)
     */
    private $officeNumber;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $emailAddress;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number")
     *
     * @Assert\NotBlank(message="common.phone_number.required")
     * @AssertPhoneNumber
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(message="assessor.voter_number.not_blank")
     * @Assert\Length(max="255")
     */
    private $voterNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     *
     * @Assert\Expression(
     *     "(this.isFrenchAssessorRequest() and value != null) or (!this.isFrenchAssessorRequest() and value == null)",
     *     message="assessor.assessor_city.not_blank",
     *     groups={"fill_assessor_info"}
     * )
     * @Assert\NotBlank(message="assessor.assessor_city.not_blank")
     * @Assert\Length(max=50)
     */
    private $assessorCity;

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Expression(
     *     "(this.isFrenchAssessorRequest() and value != null) or (!this.isFrenchAssessorRequest() and value == null)",
     *     message="assessor.assessor_postal_code.not_blank",
     *     groups={"fill_assessor_info"}
     * )
     * @Assert\Length(max=15)
     */
    private $assessorPostalCode;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank
     * @Assert\Country(message="common.country.invalid")
     */
    private $assessorCountry = AddressInterface::FRANCE;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     *
     * @Assert\NotBlank(message="assessor.office.invalid_choice")
     * @Assert\Choice(
     *     callback={"App\Entity\AssessorOfficeEnum", "toArray"},
     *     message="assessor.office.invalid_choice"
     * )
     */
    private $office = AssessorOfficeEnum::HOLDER;

    /**
     * @var ElectionVotePlace|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Election\VotePlace")
     */
    private $votePlace;

    /**
     * @var ElectionVotePlace[]|ArrayCollection
     *
     * @Assert\NotBlank(message="assessor.vote_place_wishes.not_blank", groups={"fill_assessor_info"})
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Election\VotePlace")
     * @ORM\JoinTable(name="assessor_requests_vote_place_wishes")
     */
    private $votePlaceWishes;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $processed = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $processedAt;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array")
     *
     * @Assert\NotBlank(message="assessor.election_rounds.not_blank")
     * @Assert\Choice(
     *     callback={"App\Assessor\AssessorRequestElectionRoundsEnum", "toArray"},
     *     message="assessor.election_rounds.invalid_choice",
     *     multiple=true
     * )
     */
    private $electionRounds;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public $reachable = false;

    public function __construct()
    {
        $this->phone = static::createPhoneNumber();
        $this->votePlaceWishes = new ArrayCollection();
    }

    public static function create(
        UuidInterface $uuid,
        string $gender,
        string $lastName,
        string $firstName,
        \DateTime $birthDate,
        string $birthCity,
        string $address,
        ?string $postalCode,
        string $city,
        string $voteCity,
        ?string $officeNumber,
        string $emailAddress,
        PhoneNumber $phoneNumber,
        string $voterNumber,
        ?string $assessorCity,
        ?string $assessorPostalCode,
        string $country = AddressInterface::FRANCE,
        string $office = AssessorOfficeEnum::HOLDER,
        array $electionRounds = AssessorRequestElectionRoundsEnum::ALL,
        bool $enabled = true,
        bool $reachable = false,
        string $assessorCountry = AddressInterface::FRANCE,
        ?array $votePlaceWishes = []
    ): self {
        $assessorRequest = new self();

        $assessorRequest->setUuid($uuid);
        $assessorRequest->setGender($gender);
        $assessorRequest->setLastName($lastName);
        $assessorRequest->setFirstName($firstName);
        $assessorRequest->setBirthdate($birthDate);
        $assessorRequest->setBirthCity($birthCity);
        $assessorRequest->setAddress($address);
        $assessorRequest->setPostalCode($postalCode);
        $assessorRequest->setCity($city);
        $assessorRequest->setCountry($country);
        $assessorRequest->setVoteCity($voteCity);
        $assessorRequest->setOfficeNumber($officeNumber);
        $assessorRequest->setEmailAddress($emailAddress);
        $assessorRequest->setPhone($phoneNumber);
        $assessorRequest->setVoterNumber($voterNumber);
        $assessorRequest->setAssessorCity($assessorCity);
        $assessorRequest->setAssessorPostalCode($assessorPostalCode);
        $assessorRequest->setOffice($office);
        $assessorRequest->setAssessorCountry($assessorCountry);
        $assessorRequest->setVotePlaceWishes(new ArrayCollection($votePlaceWishes));
        $assessorRequest->setReachable($reachable);
        $assessorRequest->setElectionRounds($electionRounds);

        if (!$enabled) {
            $assessorRequest->disable();
        }

        return $assessorRequest;
    }

    public function process(ElectionVotePlace $votePlace): void
    {
        $this->votePlace = $votePlace;
        $this->processed = true;
        $this->processedAt = new \DateTime();
    }

    public function unprocess(): void
    {
        $this->votePlace = null;
        $this->processed = false;
        $this->processedAt = null;
    }

    private static function createPhoneNumber(int $countryCode = 33, ?string $number = null): PhoneNumber
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode($countryCode);

        if ($number) {
            $phone->setNationalNumber($number);
        }

        return $phone;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    public function getGenderName(): ?string
    {
        return array_search($this->gender, Genders::CHOICES);
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

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
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

    public function setOfficeNumber(?string $officeNumber): void
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

    public function getVoterNumber(): ?string
    {
        return $this->voterNumber;
    }

    public function setVoterNumber(string $voterNumber): void
    {
        $this->voterNumber = $voterNumber;
    }

    public function getAssessorCity(): ?string
    {
        return $this->assessorCity;
    }

    public function setAssessorCity(?string $assessorCity): void
    {
        $this->assessorCity = $assessorCity;
    }

    public function getOffice(): string
    {
        return $this->office;
    }

    public function setOffice(string $office): void
    {
        $this->office = $office;
    }

    public function getVotePlace(): ?ElectionVotePlace
    {
        return $this->votePlace;
    }

    public function setVotePlace(ElectionVotePlace $votePlace): void
    {
        $this->votePlace = $votePlace;
    }

    public function getVotePlaceWishes(): Collection
    {
        return $this->votePlaceWishes;
    }

    public function setVotePlaceWishes(Collection $votePlaceWishes): void
    {
        $this->votePlaceWishes = $votePlaceWishes;
    }

    public function addVotePlaceWish(ElectionVotePlace $votePlace): void
    {
        if (!$this->votePlaceWishes->contains($votePlace)) {
            $this->votePlaceWishes->add($votePlace);
        }
    }

    public function removeVotePlaceWish(ElectionVotePlace $votePlace): void
    {
        $this->votePlaceWishes->removeElement($votePlace);
    }

    public function getAssessorPostalCode(): ?string
    {
        return $this->assessorPostalCode;
    }

    public function setAssessorPostalCode(?string $assessorPostalCode): void
    {
        $this->assessorPostalCode = $assessorPostalCode;
    }

    public function getAssessorCountry(): string
    {
        return $this->assessorCountry;
    }

    public function setAssessorCountry(string $assessorCountry): void
    {
        $this->assessorCountry = $assessorCountry;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function setProcessed(bool $processed): void
    {
        $this->processed = $processed;
    }

    public function getProcessedAt(): ?\DateTime
    {
        return $this->processedAt;
    }

    public function setProcessedAt(\DateTime $processedAt): void
    {
        $this->processedAt = $processedAt;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isFrenchAssessorRequest(): bool
    {
        return AddressInterface::FRANCE === $this->getAssessorCountry();
    }

    public function getOfficeName(): ?string
    {
        return array_search($this->office, AssessorOfficeEnum::CHOICES);
    }

    public function getElectionRounds(): ?array
    {
        return $this->electionRounds;
    }

    public function setElectionRounds(array $electionRounds): void
    {
        $this->electionRounds = $electionRounds;
    }

    public function setUuid(UuidInterface $uuid): void
    {
        $this->uuid = $uuid;
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

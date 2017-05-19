<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Validator\Recaptcha as AssertRecaptcha;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="procuration_requests")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcurationRequestRepository")
 *
 * @Algolia\Index(autoIndex=false)
 *
 * @UniqueEntity(fields={"emailAddress", "birthdate"}, groups={"profile"}, message="procuration.request.unique")
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
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * The associated found proxy.
     *
     * @var ProcurationProxy
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ProcurationProxy", mappedBy="foundRequest")
     */
    private $foundProxy;

    /**
     * The user who associated the found proxy.
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(name="procuration_request_found_by_id", referencedColumnName="id")
     */
    private $foundBy;

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
     * @Assert\NotBlank(message="common.phone_number.required", groups={"profile"})
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
    private $electionPresidentialFirstRound = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $electionPresidentialSecondRound = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $electionLegislativeFirstRound = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $electionLegislativeSecondRound = false;

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
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $reminded = 0;

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

    public function __toString(): string
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

    private static function createPhoneNumber(int $countryCode = 33, string $number = null): PhoneNumber
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
    public function validateChosenElections(ExecutionContextInterface $context): void
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

    public function importAdherentData(Adherent $adherent): void
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

    public function process(ProcurationProxy $procurationProxy = null, Adherent $procurationBy = null): void
    {
        $this->foundProxy = $procurationProxy;
        $this->foundBy = $procurationBy;
        $this->processed = true;
        $this->processedAt = new \DateTime();

        if ($procurationProxy) {
            $procurationProxy->setFoundRequest($this);
        }
    }

    public function unprocess(): void
    {
        if ($this->foundProxy instanceof ProcurationProxy) {
            $this->foundProxy->setFoundRequest(null);
        }

        $this->foundProxy = null;
        $this->processed = false;
        $this->processedAt = null;
    }

    public function isProxyMatching(ProcurationProxy $proxy): bool
    {
        if ($this->getVoteCountry() !== $proxy->getVoteCountry()) {
            return false;
        }

        if ($this->voteCountry === 'FR' && 0 !== strpos($proxy->getVotePostalCode(), substr($this->getVotePostalCode(), 0, 2))) {
            return false;
        }

        if ($this->getElectionPresidentialFirstRound() && !$proxy->getElectionPresidentialFirstRound()) {
            return false;
        }

        if ($this->getElectionPresidentialSecondRound() && !$proxy->getElectionPresidentialSecondRound()) {
            return false;
        }

        if ($this->getElectionLegislativeFirstRound() && !$proxy->getElectionLegislativeFirstRound()) {
            return false;
        }

        if ($this->getElectionLegislativeSecondRound() && !$proxy->getElectionLegislativeSecondRound()) {
            return false;
        }

        return true;
    }

    public function generatePrivateToken(): ?string
    {
        if (!$this->processed || !$this->foundProxy) {
            return null;
        }

        $token = Uuid::uuid5(Uuid::NAMESPACE_OID, $this->processedAt->format('Y-m-d H:i:s').$this->foundProxy->getId());

        return $token->toString();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFirstNames(): ?string
    {
        return $this->firstNames;
    }

    public function setFirstNames(?string $firstNames): void
    {
        $this->firstNames = $firstNames;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
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

    public function setCity(?string $cityCode): void
    {
        $this->city = $cityCode;

        if ($cityCode && false !== strpos($cityCode, '-')) {
            list($postalCode, $inseeCode) = explode('-', $cityCode);
            $this->cityName = (string) FranceCitiesBundle::getCity($postalCode, $inseeCode);
        }
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): void
    {
        if ($cityName) {
            $this->cityName = $cityName;
        }
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTime $birthdate): void
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

    public function getVoteCity(): ?string
    {
        return $this->voteCity;
    }

    public function setVoteCity(?string $cityCode): void
    {
        $this->voteCity = $cityCode;

        if ($cityCode && false !== strpos($cityCode, '-')) {
            list($postalCode, $inseeCode) = explode('-', $cityCode);
            $this->voteCityName = (string) FranceCitiesBundle::getCity($postalCode, $inseeCode);
        }
    }

    public function getVoteCityName(): ?string
    {
        return $this->voteCityName;
    }

    public function setVoteCityName(?string $voteCityName): void
    {
        if ($voteCityName) {
            $this->voteCityName = $voteCityName;
        }
    }

    public function getVoteCountry(): ?string
    {
        return $this->voteCountry;
    }

    public function setVoteCountry(?string $voteCountry): void
    {
        $this->voteCountry = $voteCountry;
    }

    public function getVoteOffice(): ?string
    {
        return $this->voteOffice;
    }

    public function setVoteOffice(?string $voteOffice): void
    {
        $this->voteOffice = $voteOffice;
    }

    public function getElectionPresidentialFirstRound(): bool
    {
        return $this->electionPresidentialFirstRound;
    }

    public function setElectionPresidentialFirstRound(bool $electionPresidentialFirstRound): void
    {
        $this->electionPresidentialFirstRound = $electionPresidentialFirstRound;
    }

    public function getElectionPresidentialSecondRound(): bool
    {
        return $this->electionPresidentialSecondRound;
    }

    public function setElectionPresidentialSecondRound(bool $electionPresidentialSecondRound): void
    {
        $this->electionPresidentialSecondRound = $electionPresidentialSecondRound;
    }

    public function getElectionLegislativeFirstRound(): bool
    {
        return $this->electionLegislativeFirstRound;
    }

    public function setElectionLegislativeFirstRound(bool $electionLegislativeFirstRound): void
    {
        $this->electionLegislativeFirstRound = $electionLegislativeFirstRound;
    }

    public function getElectionLegislativeSecondRound(): bool
    {
        return $this->electionLegislativeSecondRound;
    }

    public function setElectionLegislativeSecondRound(bool $electionLegislativeSecondRound): void
    {
        $this->electionLegislativeSecondRound = $electionLegislativeSecondRound;
    }

    public function getElectionsRoundsCount(): int
    {
        $count = 0;
        if ($this->electionPresidentialFirstRound) {
            ++$count;
        }

        if ($this->electionPresidentialSecondRound) {
            ++$count;
        }

        if ($this->electionLegislativeFirstRound) {
            ++$count;
        }

        if ($this->electionLegislativeSecondRound) {
            ++$count;
        }

        return $count;
    }

    public function getElections(): array
    {
        return array_filter([$this->getElectionsPresidential(), $this->getElectionsLegislative()]);
    }

    public function getElectionsPresidential(): ?string
    {
        if ($this->electionPresidentialFirstRound && $this->electionPresidentialSecondRound) {
            return 'Présidentielle : 1er et 2nd tour (23 avril et 7 mai)';
        } elseif ($this->electionPresidentialFirstRound) {
            return 'Présidentielle : 1er tour (23 avril)';
        } elseif ($this->electionPresidentialSecondRound) {
            return 'Présidentielle : 2nd tour (7 mai)';
        }

        return null;
    }

    public function getElectionsLegislative(): ?string
    {
        if ($this->electionLegislativeFirstRound && $this->electionLegislativeSecondRound) {
            return 'Législatives : 1er et 2nd tour';
        } elseif ($this->electionLegislativeFirstRound) {
            return 'Législatives : 1er tour';
        } elseif ($this->electionLegislativeSecondRound) {
            return 'Législatives : 2nd tour';
        }

        return null;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function getProcessedAt(): ?\DateTime
    {
        return $this->processedAt;
    }

    public function hasFoundProxy(): bool
    {
        return $this->foundProxy instanceof ProcurationProxy;
    }

    public function getFoundProxy(): ?ProcurationProxy
    {
        return $this->foundProxy;
    }

    public function setFoundProxy(?ProcurationProxy $procurationProxy): void
    {
        $this->foundProxy = $procurationProxy;
    }

    public function getFoundBy(): ?Adherent
    {
        return $this->foundBy;
    }

    public function setFoundBy(?Adherent $foundBy): void
    {
        $this->foundBy = $foundBy;
    }

    public function getReminded(): int
    {
        return $this->reminded;
    }

    public function isReminded(): bool
    {
        return $this->reminded > 0;
    }

    public function setReminded(int $reminder): void
    {
        $this->reminded = $reminder;
    }

    public function remind(): void
    {
        ++$this->reminded;
    }
}

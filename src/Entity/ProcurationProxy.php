<?php

namespace App\Entity;

use App\Entity\Geo\Zone;
use App\Intl\FranceCitiesBundle;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Utils\AreaUtils;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use App\Validator\ZoneType as AssertZoneType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="procuration_proxies")
 * @ORM\Entity(repositoryClass="App\Repository\ProcurationProxyRepository")
 *
 * @AssertRecaptcha(groups={"front"})
 */
class ProcurationProxy implements RecaptchaChallengeInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use RecaptchaChallengeTrait;

    public const ACTION_ENABLE = 'activer';
    public const ACTION_DISABLE = 'desactiver';
    public const ACTIONS_URI_REGEX = self::ACTION_ENABLE.'|'.self::ACTION_DISABLE;

    public const RELIABILITY_UNKNOWN = 1;
    public const RELIABILITY_ADHERENT = 4;
    public const RELIABILITY_ACTIVIST = 6;
    public const RELIABILITY_REPRESENTATIVE = 8;

    public const RELIABILITIES = [
        self::RELIABILITY_UNKNOWN,
        self::RELIABILITY_ADHERENT,
        self::RELIABILITY_ACTIVIST,
        self::RELIABILITY_REPRESENTATIVE,
    ];

    private const MAX_FRENCH_REQUESTS = 1;
    private const MAX_FOREIGN_REQUESTS_FROM_FRANCE = 2;
    private const MAX_FOREIGN_REQUESTS_FROM_FOREIGN_COUNTRY = 3;

    /**
     * The associated found request(s).
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ProcurationRequest", mappedBy="foundProxy")
     */
    private $foundRequests;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint")
     */
    private $reliability = self::RELIABILITY_UNKNOWN;

    /**
     * @var string|null
     *
     * @ORM\Column(length=30, nullable=true)
     *
     * @Assert\Length(max=30)
     */
    private $reliabilityDescription = '';

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255, groups={"front"})
     * @Assert\NotBlank(groups={"front"}, message="procuration.voter_number.not_blank")
     */
    private $voterNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6, nullable=true)
     *
     * @Assert\NotBlank(message="common.gender.not_blank", groups={"front"})
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true,
     *     groups={"front"}
     * )
     */
    private $gender;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     *
     * @Assert\NotBlank(message="procuration.last_name.not_blank", groups={"front"})
     * @Assert\Length(
     *     min=1,
     *     max=50,
     *     minMessage="procuration.last_name.min_length",
     *     maxMessage="procuration.last_name.max_length",
     *     groups={"front"}
     * )
     */
    private $lastName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, nullable=true)
     *
     * @Assert\NotBlank(groups={"front"})
     * @Assert\Length(
     *     min=2,
     *     max=100,
     *     minMessage="procuration.first_names.min_length",
     *     maxMessage="procuration.first_names.max_length",
     *     groups={"front"}
     * )
     */
    private $firstNames;

    /**
     * @var string
     *
     * @ORM\Column(length=150)
     *
     * @Assert\NotBlank(message="common.address.required", groups={"front"})
     * @Assert\Length(max=150, maxMessage="common.address.max_length", groups={"front"})
     */
    private $address = '';

    /**
     * @var string
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Length(max=15, groups={"front"})
     * @Assert\Expression(
     *     "this.getCountry() != constant('App\\Utils\\AreaUtils::CODE_FRANCE') or value != null",
     *     message="procuration.postal_code.not_empty",
     *     groups={"front"}
     * )
     */
    private $postalCode = '';

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true, name="city_insee")
     *
     * @Assert\Length(max=15, groups={"front"})
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255, groups={"front"})
     */
    private $cityName;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255, groups={"front"})
     * @Assert\Expression(
     *     "not (this.getCountry() == constant('App\\Utils\\AreaUtils::CODE_FRANCE') and value != null)",
     *     message="procuration.state.not_empty",
     *     groups={"front"}
     * )
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank(groups={"front"})
     * @AssertUnitedNationsCountry(message="common.country.invalid", groups={"front"})
     */
    private $country = AreaUtils::CODE_FRANCE;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @Assert\NotBlank(message="common.phone_number.required", groups={"front"})
     * @AssertPhoneNumber(defaultRegion="FR", groups={"front"})
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(groups={"front"})
     * @Assert\Email(message="common.email.invalid", groups={"front"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"front"})
     */
    private $emailAddress = '';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Assert\NotBlank(message="procuration.birthdate.not_blank", groups={"front"})
     * @Assert\Range(
     *     min="-120 years",
     *     max="-17 years",
     *     minMessage="procuration.birthdate.maximum_required_age",
     *     maxMessage="procuration.birthdate.minimum_required_age",
     *     groups={"front"}
     * )
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Length(max=15, groups={"front"})
     * @Assert\Expression(
     *     "(this.getVoteCountry() == constant('App\\Utils\\AreaUtils::CODE_FRANCE') and value != null) or (this.getVoteCountry() != constant('App\\Utils\\AreaUtils::CODE_FRANCE') and value == null)",
     *     message="procuration.postal_code.not_empty",
     *     groups={"front"}
     * )
     */
    private $votePostalCode = '';

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true, name="vote_city_insee")
     *
     * @Assert\Length(max=15, groups={"front"})
     */
    private $voteCity;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255, groups={"front"})
     */
    private $voteCityName;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank(groups={"front"})
     * @AssertUnitedNationsCountry(message="common.country.invalid", groups={"front"})
     */
    private $voteCountry = AreaUtils::CODE_FRANCE;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(groups={"front"})
     * @Assert\Length(max=50, groups={"front"})
     */
    private $voteOffice = '';

    /**
     * @var ProcurationProxyElectionRound[]|Collection
     *
     * @ORM\OneToMany(targetEntity="ProcurationProxyElectionRound", mappedBy="procurationProxy", orphanRemoval=true, cascade={"all"})
     */
    private $procurationProxyElectionRounds;

    private $electionRounds;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $disabled = false;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $disabledReason = null;

    /**
     * @var int
     *
     * @Assert\Range(
     *     min=1,
     *     max=3,
     *     groups={"front", "Default"}
     * )
     * @Assert\Expression(
     *     "(this.getVoteCountry() == constant('App\\Utils\\AreaUtils::CODE_FRANCE') and value <= 2) or (this.getVoteCountry() != constant('App\\Utils\\AreaUtils::CODE_FRANCE') and value <= 3)",
     *     message="procuration.vote_country.conditions",
     *     groups={"front", "Default"}
     * )
     * @Assert\Expression(
     *     "this.getFoundRequests().count() <= value",
     *     message="procuration.proxies_count.already_associated"
     * )
     *
     * @ORM\Column(type="smallint", options={"default": 1, "unsigned": true})
     */
    public $proxiesCount = 1;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    public $frenchRequestAvailable = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    public $foreignRequestAvailable = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public $reachable = false;

    /**
     * @ORM\Column(nullable=true)
     */
    private $backupOtherVoteCities;

    /**
     * @var Collection|Zone[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone")
     *
     * @AssertZoneType(types={"city", "borough"}, groups={"front"})
     */
    private Collection $otherVoteCities;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $remindedAt = null;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->phone = static::createPhoneNumber();
        $this->procurationProxyElectionRounds = new ArrayCollection();
        $this->foundRequests = new ArrayCollection();
        $this->otherVoteCities = new ArrayCollection();
    }

    public function __toString()
    {
        return 'Proposition de procuration de '.$this->lastName.' '.$this->firstNames;
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

    public function importAdherentData(Adherent $adherent)
    {
        $this->gender = $adherent->getGender();
        $this->setFirstNames($adherent->getFirstName());
        $this->setLastName($adherent->getLastName());
        $this->emailAddress = $adherent->getEmailAddress();
        $this->setAddress($adherent->getAddress());
        $this->postalCode = $adherent->getPostalCode();
        $this->city = $adherent->getCity();
        $this->setCityName($adherent->getCityName());
        $this->country = $adherent->getCountry();
        $this->phone = $adherent->getPhone();
        $this->birthdate = $adherent->getBirthdate();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReliability(): ?int
    {
        return $this->reliability;
    }

    public function setReliability(?int $reliability): void
    {
        $this->reliability = $reliability;
    }

    public function getReliabilityDescription(): ?string
    {
        return $this->reliabilityDescription;
    }

    public function setReliabilityDescription(?string $reliabilityDescription): void
    {
        $this->reliabilityDescription = $reliabilityDescription;
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
        $this->emailAddress = mb_strtolower($emailAddress);
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function getVotePostalCode(): ?string
    {
        return $this->votePostalCode;
    }

    public function setVotePostalCode(?string $votePostalCode): void
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
            $inseeCode = str_pad($inseeCode, 5, '0', \STR_PAD_LEFT);

            $this->voteCity = "$postalCode-$inseeCode";
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

    public function getVoteOffice(): string
    {
        return $this->voteOffice;
    }

    public function setVoteOffice(string $voteOffice): void
    {
        $this->voteOffice = $voteOffice;
    }

    /**
     * @return ProcurationProxyElectionRound[]|Collection
     */
    public function getAvailableRounds(?ProcurationRequest $request = null): Collection
    {
        return $this->procurationProxyElectionRounds->filter(function (ProcurationProxyElectionRound $ppElectionRound) use ($request) {
            if ((!$request && ($ppElectionRound->isFrenchRequestAvailable() || $ppElectionRound->isForeignRequestAvailable()))
                || ($request && (($request->isRequestFromFrance() && $ppElectionRound->isFrenchRequestAvailable())
                    || (!$request->isRequestFromFrance() && $ppElectionRound->isForeignRequestAvailable())))) {
                return $ppElectionRound->getElectionRound();
            }
        });
    }

    public function setElectionRounds(iterable $rounds): void
    {
        // Initialize the collection if not done already
        $currentRoundsToRemove = new ArrayCollection($this->procurationProxyElectionRounds->toArray());

        foreach ($rounds as $round) {
            if (!$round instanceof ElectionRound) {
                throw new \InvalidArgumentException(sprintf('Expected an instance of "%s", but got "%s".', ElectionRound::class, \is_object($round) ? \get_class($round) : \gettype($round)));
            }

            if ($ppElectionRound = $this->findProcurationProxyElectionRoundBy($round)) {
                $currentRoundsToRemove->removeElement($ppElectionRound);

                continue;
            }

            $this->addElectionRound($round);
        }

        foreach ($currentRoundsToRemove as $round) {
            $this->procurationProxyElectionRounds->removeElement($round);
        }
    }

    /**
     * @return ProcurationProxyElectionRound[]|Collection
     */
    public function getProcurationProxyElectionRounds(): Collection
    {
        return $this->procurationProxyElectionRounds;
    }

    /**
     * @return ElectionRound[]|Collection
     */
    public function getElectionRounds(): Collection
    {
        return new ArrayCollection(array_map(function (ProcurationProxyElectionRound $ppElectionRound) {
            return $ppElectionRound->getElectionRound();
        }, $this->procurationProxyElectionRounds->toArray()));
    }

    public function addElectionRound(ElectionRound $round): void
    {
        if (!$this->hasElectionRound($round)) {
            $this->procurationProxyElectionRounds->add(new ProcurationProxyElectionRound($this, $round));
        }
    }

    public function removeElectionRound(ElectionRound $round): void
    {
        if ($electionRound = $this->findProcurationProxyElectionRoundBy($round)) {
            $this->procurationProxyElectionRounds->removeElement($electionRound);
        }
    }

    public function findProcurationProxyElectionRoundBy(ElectionRound $round): ?ProcurationProxyElectionRound
    {
        $electionRounds = $this->procurationProxyElectionRounds->filter(function (ProcurationProxyElectionRound $ppElectionRound) use ($round) {
            return $ppElectionRound->getElectionRound() === $round;
        });

        return $electionRounds->count() > 0 ? $electionRounds->first() : null;
    }

    public function hasElectionRound(ElectionRound $round): bool
    {
        return null !== $this->findProcurationProxyElectionRoundBy($round);
    }

    public function getElection(): Election
    {
        return $this->procurationProxyElectionRounds->current()->getElectionRound()->getElection();
    }

    /**
     * @return ProcurationRequest[]|Collection
     */
    public function getFoundRequests(): Collection
    {
        return $this->foundRequests;
    }

    /**
     * @return ProcurationRequest[]|Collection
     */
    public function getFoundRequestsByElectionRound(ElectionRound $round): Collection
    {
        return $this->getFoundRequests()->filter(function (ProcurationRequest $request) use ($round) {
            return $request->getElectionRounds()->contains($round);
        });
    }

    private function addFoundRequest(ProcurationRequest $procurationRequest): void
    {
        if (!$this->foundRequests->contains($procurationRequest)) {
            $this->foundRequests->add($procurationRequest);
            $procurationRequest->setFoundProxy($this);

            $this->processAvailabilitiesFor($procurationRequest);
        }
    }

    private function removeFoundRequest(ProcurationRequest $procurationRequest): void
    {
        $this->foundRequests->removeElement($procurationRequest);
        $procurationRequest->setFoundProxy(null);

        $this->processAvailabilitiesFor($procurationRequest);
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }

    public function enable(): void
    {
        $this->disabled = false;
    }

    public function disable(?string $reason = null): void
    {
        $this->disabled = true;
        $this->disabledReason = $reason;
    }

    public function matchesRequest(ProcurationRequest $request): bool
    {
        if ($this->voteCountry !== $request->getVoteCountry()) {
            return false;
        }

        foreach ($request->getElectionRounds() as $round) {
            if (!$this->hasElectionRound($round)) {
                return false;
            }
        }

        return true;
    }

    public function getProxiesCount(): int
    {
        return $this->proxiesCount;
    }

    public function setProxiesCount(int $proxiesCount): void
    {
        $this->proxiesCount = $proxiesCount;
    }

    public function process(ProcurationRequest $request): void
    {
        $this->addFoundRequest($request);
    }

    public function unprocess(ProcurationRequest $request): void
    {
        $this->removeFoundRequest($request);
    }

    public function processAvailabilitiesFor(ProcurationRequest $request): void
    {
        foreach ($request->getElectionRounds() as $round) {
            $ppElectionRound = $this->findProcurationProxyElectionRoundBy($round);
            $this->processFrenchAvailability($ppElectionRound);
            $this->processForeignAvailability($ppElectionRound);
        }
    }

    public function processAvailabilities(): void
    {
        foreach ($this->getProcurationProxyElectionRounds() as $ppElectionRound) {
            $ppElectionRound->setFrenchRequestAvailable($this->hasFreeSlots($round = $ppElectionRound->getElectionRound())
                && self::MAX_FRENCH_REQUESTS > $this->countFrenchRequests($round));
        }

        foreach ($this->getProcurationProxyElectionRounds() as $ppElectionRound) {
            $ppElectionRound->setForeignRequestAvailable($this->hasFreeSlots($round = $ppElectionRound->getElectionRound())
                && $this->getForeignRequestsLimit() > $this->countForeignRequests($round));
        }
    }

    private function processFrenchAvailability(ProcurationProxyElectionRound $ppElectionRound): void
    {
        $ppElectionRound->setFrenchRequestAvailable($this->hasFreeSlots($round = $ppElectionRound->getElectionRound())
            && self::MAX_FRENCH_REQUESTS > $this->countFrenchRequests($round));
    }

    private function processForeignAvailability(ProcurationProxyElectionRound $ppElectionRound): void
    {
        $ppElectionRound->setForeignRequestAvailable($this->hasFreeSlots($round = $ppElectionRound->getElectionRound())
            && $this->getForeignRequestsLimit() > $this->countForeignRequests($round));
    }

    private function getForeignRequestsLimit(): int
    {
        return AreaUtils::CODE_FRANCE === $this->getVoteCountry()
            ? self::MAX_FOREIGN_REQUESTS_FROM_FRANCE
            : self::MAX_FOREIGN_REQUESTS_FROM_FOREIGN_COUNTRY;
    }

    public function isReachable(): bool
    {
        return $this->reachable;
    }

    public function setReachable(bool $reachable): void
    {
        $this->reachable = $reachable;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    private function hasFreeSlots(ElectionRound $round): bool
    {
        return 0 < $this->countFreeSlots($round);
    }

    public function countFreeSlots(ElectionRound $round): int
    {
        return $this->proxiesCount - $this->getFoundRequestsByElectionRound($round)->count();
    }

    private function countFrenchRequests(ElectionRound $round): int
    {
        return $this
            ->getFoundRequests()
            ->filter(function (ProcurationRequest $request) use ($round) {
                return true === $request->isRequestFromFrance() && $request->getElectionRounds()->contains($round);
            })
            ->count()
        ;
    }

    private function countForeignRequests(ElectionRound $round): int
    {
        return $this
            ->getFoundRequests()
            ->filter(function (ProcurationRequest $request) use ($round) {
                return false === $request->isRequestFromFrance() && $request->getElectionRounds()->contains($round);
            })
            ->count()
        ;
    }

    public function setRepresentativeReliability(): void
    {
        $this->reliability = self::RELIABILITY_REPRESENTATIVE;
    }

    public function setActivistReliability(): void
    {
        $this->reliability = self::RELIABILITY_ACTIVIST;
    }

    public function setAdherentReliability(): void
    {
        $this->reliability = self::RELIABILITY_ADHERENT;
    }

    public function getVoterNumber(): ?string
    {
        return $this->voterNumber;
    }

    public function setVoterNumber(?string $voterNumber): void
    {
        $this->voterNumber = $voterNumber;
    }

    /** @return Zone[] */
    public function getOtherVoteCities(): array
    {
        return $this->otherVoteCities->toArray();
    }

    public function addOtherVoteCity(Zone $city): void
    {
        if (!$this->otherVoteCities->contains($city)) {
            $this->otherVoteCities->add($city);
        }
    }

    public function removeOtherVoteCity(Zone $city): void
    {
        $this->otherVoteCities->removeElement($city);
    }

    public function remind(): void
    {
        $this->remindedAt = new \DateTime();
    }
}

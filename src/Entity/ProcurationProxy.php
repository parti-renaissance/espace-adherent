<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Validator\Recaptcha as AssertRecaptcha;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="procuration_proxies")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcurationProxyRepository")

 * @Algolia\Index(autoIndex=false)
 */
class ProcurationProxy
{
    use EntityTimestampableTrait;
    use ElectionRoundsCollectionTrait;

    public const ACTION_ENABLE = 'activer';
    public const ACTION_DISABLE = 'desactiver';
    public const ACTIONS_URI_REGEX = self::ACTION_ENABLE.'|'.self::ACTION_DISABLE;

    private const NO_AVAILABLE_ROUND = 'Aucun';
    private const ALL_AVAILABLE_ROUNDS = 'Tous les tours proposés';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * The referent who invited this proxy.
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $referent;

    /**
     * The associated found request(s).
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ProcurationRequest", mappedBy="foundProxy")
     */
    private $foundRequests;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint")
     */
    private $reliability = 0;

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
     * @ORM\Column(length=6)
     *
     * @Assert\NotBlank(message="common.gender.invalid_choice", groups={"front"})
     * @Assert\Choice(
     *     callback={"AppBundle\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true,
     *     groups={"front"}
     * )
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="procuration.last_name.not_blank", groups={"front"})
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="procuration.last_name.min_length",
     *     maxMessage="procuration.last_name.max_length",
     *     groups={"front"}
     * )
     */
    private $lastName = '';

    /**
     * @var string
     *
     * @ORM\Column(length=100)
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
    private $firstNames = '';

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
     *     "(this.getCountry() == 'FR' and value != null) or (this.getCountry() != 'FR' and value == null)",
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
     *     "(this.getCountry() == 'FR' and value == null) or (this.getCountry() != 'FR' and value != null)",
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
    private $country = 'FR';

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
     * @Assert\Range(max="-17 years", maxMessage="procuration.birthdate.minimum_required_age", groups={"front"})
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Length(max=15, groups={"front"})
     * @Assert\Expression(
     *     "(this.getVoteCountry() == 'FR' and value != null) or (this.getVoteCountry() != 'FR' and value == null)",
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
    private $voteCountry = 'FR';

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
     * @var ElectionRound[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ElectionRound")
     * @ORM\JoinTable(name="procuration_proxies_to_election_rounds")
     *
     * @Assert\Count(min=1, minMessage="procuration.election_rounds.min_count", groups={"front"})
     */
    private $electionRounds;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $disabled = false;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, nullable=true)
     *
     * @Assert\Length(max=100, groups={"front"})
     */
    private $inviteSourceName = '';

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, nullable=true)
     *
     * @Assert\Length(max=100, groups={"front"})
     */
    private $inviteSourceFirstName = '';

    /**
     * @var string
     *
     * @Assert\NotBlank(message="common.recaptcha.invalid_message", groups={"front"})
     * @AssertRecaptcha(groups={"front"})
     */
    public $recaptcha = '';

    /**
     * @var int
     *
     * @Assert\Range(
     *     min=1,
     *     max=3,
     *     groups={"front"}
     * )
     * @Assert\Expression(
     *     "(this.getVoteCountry() == 'FR' and value <= 2) or (this.getVoteCountry() != 'FR' and value <= 3)",
     *     message="procuration.vote_country.conditions",
     *     groups={"front"}
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

    public function __construct(?Adherent $referent)
    {
        $this->referent = $referent;
        $this->phone = static::createPhoneNumber();
        $this->electionRounds = new ArrayCollection();
        $this->foundRequests = new ArrayCollection();

        if (!$this->referent) {
            $this->reliability = -1;
        }
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

    public function getReferent(): ?Adherent
    {
        return $this->referent;
    }

    public function setReferent(Adherent $referent = null): void
    {
        $this->referent = $referent;
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
        $this->emailAddress = $emailAddress;
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
            $this->voteCityName = (string) FranceCitiesBundle::getCity($postalCode, $inseeCode);
        }
    }

    public function getVoteCityName(): ?String
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
     * @return ElectionRound[]|Collection
     */
    public function getAvailableRounds(): Collection
    {
        if ($this->foundRequests->isEmpty()) {
            return $this->electionRounds;
        }

        $availableRounds = new ArrayCollection();

        for ($i = 0; $i < $this->proxiesCount; ++$i) {
            foreach ($this->electionRounds as $round) {
                $availableRounds->add($round);
            }
        }

        foreach ($this->foundRequests as $procurationRequest) {
            foreach ($procurationRequest->getElectionRounds() as $round) {
                if ($availableRounds->contains($round)) {
                    $availableRounds->removeElement($round);
                }
            }
        }

        return $availableRounds;
    }

    public function getAvailableRoundsAsString(): string
    {
        $availableRounds = $this->getAvailableRounds();

        if ($this->electionRounds->count() === $availableRounds->count()) {
            return self::ALL_AVAILABLE_ROUNDS;
        }

        if ($availableRounds->isEmpty()) {
            return self::NO_AVAILABLE_ROUND;
        }

        return implode("\n", $availableRounds->toArray());
    }

    public function getFoundRequests(): Collection
    {
        return $this->foundRequests;
    }

    private function addFoundRequest(ProcurationRequest $procurationRequest): void
    {
        if (!$this->foundRequests->contains($procurationRequest)) {
            $this->foundRequests->add($procurationRequest);
            $procurationRequest->setFoundProxy($this);
        }
    }

    private function removeFoundRequest(ProcurationRequest $procurationRequest): void
    {
        $this->foundRequests->removeElement($procurationRequest);
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

    public function disable(): void
    {
        $this->disabled = true;
    }

    public function getInviteSourceName(): ?string
    {
        return $this->inviteSourceName;
    }

    public function setInviteSourceName(?string $inviteSourceName): void
    {
        $this->inviteSourceName = $inviteSourceName;
    }

    public function getInviteSourceFirstName(): ?string
    {
        return $this->inviteSourceFirstName;
    }

    public function setInviteSourceFirstName(?string $inviteSourceFirstName): void
    {
        $this->inviteSourceFirstName = $inviteSourceFirstName;
    }

    public function matchesRequest(ProcurationRequest $request): bool
    {
        if ($this->voteCountry !== $request->getVoteCountry()) {
            return false;
        }

        if ('FR' === $this->voteCountry && 0 !== strpos($request->getVotePostalCode(), substr($this->votePostalCode, 0, 2))) {
            return false;
        }

        foreach ($request->getElectionRounds() as $round) {
            if (!$this->electionRounds->contains($round)) {
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

    public function isFrenchRequestAvailable(): bool
    {
        return $this->frenchRequestAvailable;
    }

    private function setFrenchRequestAvailable(bool $frenchRequestAvailable): void
    {
        $this->frenchRequestAvailable = $frenchRequestAvailable;
    }

    public function isForeignRequestAvailable(): bool
    {
        return $this->foreignRequestAvailable;
    }

    private function setForeignRequestAvailable(bool $foreignRequestAvailable): void
    {
        $this->foreignRequestAvailable = $foreignRequestAvailable;
    }

    public function process(ProcurationRequest $request): void
    {
        $proxiesUsedCount = $this->getFoundRequests()->count();
        $remainingProxiesCount = $this->proxiesCount - $proxiesUsedCount;

        if (1 === $remainingProxiesCount) {
            $this->setFrenchRequestAvailable(false);
            $this->setForeignRequestAvailable(false);
        } else {
            if ('FR' === $this->getVoteCountry()) {
                $this->setFrenchRequestAvailable(false);
            } else {
                if (2 === $remainingProxiesCount && !$this->isFrenchRequestAvailable()) {
                    $this->setForeignRequestAvailable(false);
                }
            }
        }

        $this->addFoundRequest($request);
    }

    public function unprocess(ProcurationRequest $request): void
    {
        $proxiesUsedCount = $this->getFoundRequests()->count();
        $remainingProxiesCount = $this->proxiesCount - $proxiesUsedCount;

        if (1 === $this->getFoundRequests()->count()) {
            $this->setFrenchRequestAvailable(true);
            $this->setForeignRequestAvailable(true);
        } else {
            if ('FR' === $this->getVoteCountry()) {
                $this->setFrenchRequestAvailable(true);
            } else {
                $this->setForeignRequestAvailable(true);

                if (0 === $remainingProxiesCount) {
                    $this->setFrenchRequestAvailable(true);
                }
            }
        }

        $this->removeFoundRequest($request);
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
}

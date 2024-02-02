<?php

namespace App\Entity;

use App\Address\AddressInterface;
use App\Procuration\ProcurationDisableReasonEnum;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="procuration_requests")
 * @ORM\Entity(repositoryClass="App\Repository\ProcurationRequestRepository")
 *
 * @AssertRecaptcha(groups={"elections"})
 */
class ProcurationRequest implements RecaptchaChallengeInterface
{
    use EntityTimestampableTrait;
    use ElectionRoundsCollectionTrait;
    use RecaptchaChallengeTrait;

    public const STEP_VOTE = 'vote';
    public const STEP_PROFILE = 'profile';
    public const STEP_ELECTION_ROUNDS = 'election_rounds';
    public const STEP_THANKS = 'thanks';

    public const STEP_URI_VOTE = 'mon-lieu-de-vote';
    public const STEP_URI_PROFILE = 'mes-coordonnees';
    public const STEP_URI_ELECTION_ROUNDS = 'ma-procuration';
    public const STEP_URI_THANKS = 'merci';

    public const STEPS = [
        self::STEP_URI_VOTE => self::STEP_VOTE,
        self::STEP_URI_PROFILE => self::STEP_PROFILE,
        self::STEP_URI_ELECTION_ROUNDS => self::STEP_ELECTION_ROUNDS,
    ];

    public const STEP_URIS = [
        1 => self::STEP_URI_VOTE,
        2 => self::STEP_URI_PROFILE,
        3 => self::STEP_URI_ELECTION_ROUNDS,
        4 => self::STEP_URI_THANKS,
    ];

    public const ACTION_PROCESS = 'traiter';
    public const ACTION_UNPROCESS = 'detraiter';
    public const ACTION_ENABLE = 'activer';
    public const ACTION_DISABLE = 'desactiver';
    public const ACTIVATION_ACTIONS = [
        self::ACTION_ENABLE,
        self::ACTION_DISABLE,
    ];
    public const ACTIONS_URI_REGEX = self::ACTION_PROCESS.'|'.self::ACTION_UNPROCESS.'|'.self::ACTION_ENABLE.'|'.self::ACTION_DISABLE;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\ProcurationProxy", inversedBy="foundRequests")
     */
    private $foundProxy;

    /**
     * The user who associated the found proxy.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(name="procuration_request_found_by_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $foundBy;

    /**
     * @var string|null
     *
     * @ORM\Column(length=6)
     *
     * @Assert\NotBlank(message="common.gender.invalid_choice", groups={"profile"})
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     groups={"profile"}
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
     *     min=1,
     *     max=50,
     *     minMessage="procuration.last_name.min_length",
     *     maxMessage="procuration.last_name.max_length",
     *     groups={"profile"}
     * )
     */
    private $lastName = '';

    /**
     * @var string
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank(groups={"profile"})
     * @Assert\Length(
     *     min=2,
     *     max=100,
     *     minMessage="procuration.first_names.min_length",
     *     maxMessage="procuration.first_names.max_length",
     *     groups={"profile"}
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
     * @Assert\Expression(
     *     "this.getCountry() != constant('App\\Address\\Address::FRANCE') or value != null",
     *     message="procuration.postal_code.not_empty",
     *     groups={"profile"}
     * )
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
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255, groups={"profile"})
     * @Assert\Expression(
     *     "not (this.getCountry() == constant('App\\Address\\Address::FRANCE') and value != null)",
     *     message="procuration.state.not_empty",
     *     groups={"profile"}
     * )
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank(groups={"profile"})
     * @Assert\Country(message="common.country.invalid", groups={"profile"})
     */
    private $country = AddressInterface::FRANCE;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @Assert\NotBlank(message="common.phone_number.required", groups={"profile"})
     * @AssertPhoneNumber(options={"groups": {"profile"}})
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(groups={"profile"})
     * @Assert\Email(message="common.email.invalid", groups={"profile"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"profile"})
     */
    private $emailAddress = '';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Assert\NotBlank(message="procuration.birthdate.not_blank", groups={"profile"})
     * @Assert\Range(
     *     min="-120 years",
     *     max="-17 years",
     *     minMessage="procuration.birthdate.maximum_required_age",
     *     maxMessage="procuration.birthdate.minimum_required_age",
     *     groups={"profile"}
     * )
     */
    private $birthdate;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\NotBlank(groups={"profile"}, message="procuration.voter_number.not_blank")
     * @Assert\Length(max=255, groups={"profile"})
     */
    private $voterNumber;

    /**
     * @var string
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Length(max=15, groups={"vote"})
     * @Assert\Expression(
     *     "(this.getVoteCountry() == constant('App\\Address\\Address::FRANCE') and value != null) or (this.getVoteCountry() != constant('App\\Address\\Address::FRANCE') and value == null)",
     *     message="procuration.postal_code.not_empty",
     *     groups={"vote"}
     * )
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
     * @Assert\Country(message="common.country.invalid", groups={"vote"})
     */
    private $voteCountry = AddressInterface::FRANCE;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(groups={"vote"})
     * @Assert\Length(max=50, groups={"vote"})
     */
    private $voteOffice = '';

    /**
     * @var ElectionRound[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ElectionRound")
     * @ORM\JoinTable(name="procuration_requests_to_election_rounds")
     *
     * @Assert\Count(min=1, minMessage="procuration.election_rounds.min_count", groups={"election_rounds"})
     */
    private $electionRounds;

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
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $remindedAt = null;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $requestFromFrance = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public $reachable = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $enabled = false;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $disabledReason = null;

    public function __construct()
    {
        $this->phone = static::createPhoneNumber();
        $this->electionRounds = new ArrayCollection();
    }

    public function __toString(): string
    {
        return 'Demande de procuration de '.$this->lastName.' '.$this->firstNames;
    }

    public function importAdherentData(Adherent $adherent): void
    {
        $this->gender = $adherent->getGender();
        $this->firstNames = $adherent->getFirstName();
        $this->lastName = $adherent->getLastName();
        $this->emailAddress = $adherent->getEmailAddress();
        $this->address = $adherent->getAddress();
        $this->postalCode = $adherent->getPostalCode();
        $this->setCity($adherent->getCity());
        $this->cityName = $adherent->getCityName();
        $this->country = $adherent->getCountry();
        $this->phone = $adherent->getPhone();
        $this->birthdate = $adherent->getBirthdate();
    }

    public function process(?ProcurationProxy $procurationProxy = null, ?Adherent $procurationFoundBy = null): void
    {
        $this->foundBy = $procurationFoundBy;
        $this->processed = true;
        $this->processedAt = new \DateTimeImmutable();

        if ($procurationProxy) {
            $procurationProxy->process($this);
        }
    }

    public function unprocess(): void
    {
        if ($this->foundProxy instanceof ProcurationProxy) {
            $this->foundProxy->unprocess($this);
        }

        $this->foundBy = null;
        $this->processed = false;
        $this->processedAt = null;
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

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTime $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function getVoterNumber(): ?string
    {
        return $this->voterNumber;
    }

    public function setVoterNumber(?string $voterNumber): void
    {
        $this->voterNumber = $voterNumber;
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

    public function getVoteCityInsee(): ?string
    {
        return mb_substr($this->voteCity, -5);
    }

    public function setVoteCity(?string $cityCode): void
    {
        $this->voteCity = $cityCode;

        if ($cityCode && str_contains($cityCode, '-')) {
            [$postalCode, $inseeCode] = explode('-', $cityCode);
            $inseeCode = str_pad($inseeCode, 5, '0', \STR_PAD_LEFT);

            $this->voteCity = "$postalCode-$inseeCode";
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

    public function setVoteOffice(string $voteOffice): void
    {
        $this->voteOffice = $voteOffice;
    }

    public function getElectionRoundsCount(): int
    {
        return $this->electionRounds->count();
    }

    public function getElectionRoundLabels(): array
    {
        return array_map(function (ElectionRound $round) {
            return $round->getLabel();
        }, $this->electionRounds->toArray());
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

    public function remind(): void
    {
        $this->remindedAt = new \DateTime();
    }

    public static function getStepForUri(string $stepUri): string
    {
        if (!isset(self::STEPS[$stepUri])) {
            throw new \InvalidArgumentException(sprintf('Invalid step uri "%s". Valid step uris are: "%s".', $stepUri, implode('", "', self::STEP_URIS)));
        }

        return self::STEPS[$stepUri];
    }

    public static function getNextStepUri(string $currentStepUri): ?string
    {
        if (false === $step = array_search($currentStepUri, self::STEP_URIS, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid step "%s". Valid steps are: "%s".', $currentStepUri, implode('", "', self::STEP_URIS)));
        }

        return self::STEP_URIS[++$step] ?? null;
    }

    public static function isFinalStepUri(string $currentStepUri): bool
    {
        $stepUris = array_keys(self::STEPS);

        return $currentStepUri === end($stepUris);
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

    public function isRequestFromFrance(): bool
    {
        return $this->requestFromFrance;
    }

    public function setRequestFromFrance(bool $requestFromFrance): void
    {
        $this->requestFromFrance = $requestFromFrance;
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

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getDisabledReason(): ?string
    {
        return $this->disabledReason;
    }

    public function isAutoDisabled(): bool
    {
        return !$this->enabled && \in_array($this->disabledReason, ProcurationDisableReasonEnum::AUTO_DISABLED_REASONS, true);
    }

    public function disable(?string $reason): void
    {
        $this->setEnabled(false);
        $this->disabledReason = $reason;
    }

    public function enable(): void
    {
        $this->setEnabled(true);
        $this->disabledReason = null;
    }
}

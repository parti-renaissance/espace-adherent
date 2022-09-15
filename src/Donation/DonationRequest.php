<?php

namespace App\Donation;

use App\Address\Address;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Form\DonationRequestType;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Renaissance\Donation\DonationRequestStateEnum;
use App\Validator\FrenchAddressOrNationalityDonation;
use App\Validator\MaxFiscalYearDonation;
use App\Validator\MaxMonthDonation;
use App\Validator\PayboxSubscription as AssertPayboxSubscription;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\UniqueDonationSubscription;
use App\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use App\ValueObject\Genders;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueDonationSubscription(groups={"Default", "fill_personal_info"})
 * @FrenchAddressOrNationalityDonation(groups={"Default", "fill_personal_info"})
 * @MaxFiscalYearDonation(groups={"Default", "fill_personal_info"})
 * @MaxMonthDonation(groups={"Default", "choose_donation_amount"})
 * @AssertRecaptcha(groups={"donation_request_mentions"})
 */
class DonationRequest implements DonationRequestInterface, RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    public const DEFAULT_AMOUNT = 50.0;
    public const ALERT_AMOUNT = 200;

    private string $state = DonationRequestStateEnum::STATE_DONATION_AMOUNT;

    private $uuid;

    /**
     * @Assert\NotBlank(groups={"Default", "choose_donation_amount"})
     * @Assert\GreaterThan(value=0, message="donation.amount.greater_than_0", groups={"Default", "choose_donation_amount"})
     */
    private $amount;

    /**
     * @Assert\NotBlank(message="common.gender.invalid_choice", groups={"Default", "fill_personal_info"})
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true,
     *     groups={"Default", "fill_personal_info"}
     * )
     */
    public $gender;

    /**
     * @Assert\NotBlank(groups={"Default", "fill_personal_info"})
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length",
     *     groups={"Default", "fill_personal_info"}
     * )
     */
    public $firstName;

    /**
     * @Assert\NotBlank(groups={"Default", "fill_personal_info"})
     * @Assert\Length(
     *     min=1,
     *     max=50,
     *     minMessage="common.last_name.min_length",
     *     maxMessage="common.last_name.max_length",
     *     groups={"Default", "fill_personal_info"}
     * )
     */
    public $lastName;

    /**
     * @Assert\NotBlank(groups={"Default", "fill_personal_info"})
     * @Assert\Email(message="common.email.invalid", groups={"Default", "fill_personal_info"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"Default", "fill_personal_info"})
     */
    private $emailAddress;

    /**
     * @Assert\NotBlank(message="common.address.required", groups={"Default", "fill_personal_info"})
     * @Assert\Length(max=150, maxMessage="common.address.max_length", groups={"Default", "fill_personal_info"})
     */
    private $address;

    /**
     * @Assert\NotBlank(groups={"Default", "fill_personal_info"})
     * @Assert\Length(max=15, groups={"Default", "fill_personal_info"})
     */
    private $postalCode;

    /**
     * @Assert\Length(max=15, groups={"Default", "fill_personal_info"})
     */
    private $city;

    /**
     * @Assert\Length(max=255, groups={"Default", "fill_personal_info"})
     */
    private $cityName;

    /**
     * @Assert\NotBlank(groups={"Default", "fill_personal_info"})
     * @AssertUnitedNationsCountry(message="common.country.invalid", groups={"Default", "fill_personal_info"})
     */
    private $country;

    /**
     * @Assert\NotBlank(groups={"Default", "fill_personal_info"})
     * @Assert\Country(message="common.nationality.invalid", groups={"Default", "fill_personal_info"})
     */
    private $nationality;

    /**
     * @Assert\Regex(pattern="/^[\d]{6}$/", message="donation.code.invalid")
     */
    private $code;

    private $clientIp;

    /**
     * @AssertPayboxSubscription(groups={"Default", "choose_donation_amount"})
     */
    private $duration;

    /**
     * @Assert\Choice(DonationRequestType::CONFIRM_DONATION_TYPE_CHOICES, groups={"donation_confirm_type"})
     */
    private $confirmDonationType = DonationRequestType::CONFIRM_DONATION_TYPE_UNIQUE;

    /**
     * @Assert\Range(min=0, max=7500, groups={"donation_confirm_type"})
     */
    private $confirmSubscriptionAmount;

    private $type;

    private ?string $source = null;

    private ?int $adherentId = null;

    public function __construct(
        UuidInterface $uuid = null,
        string $clientIp = null,
        float $amount = self::DEFAULT_AMOUNT,
        int $duration = PayboxPaymentSubscription::NONE,
        string $type = Donation::TYPE_CB
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->clientIp = $clientIp;
        $this->emailAddress = '';
        $this->country = Address::FRANCE;
        $this->setAmount($amount);
        $this->duration = $duration;
        $this->type = $type;
    }

    public static function createFromAdherent(
        Adherent $adherent,
        string $clientIp,
        float $amount = self::DEFAULT_AMOUNT,
        int $duration = PayboxPaymentSubscription::NONE
    ): self {
        $dto = new self(Uuid::uuid4(), $clientIp, $amount, $duration);
        $dto->gender = $adherent->getGender();
        $dto->firstName = $adherent->getFirstName();
        $dto->lastName = $adherent->getLastName();
        $dto->emailAddress = $adherent->getEmailAddress();
        $dto->address = $adherent->getAddress();
        $dto->postalCode = $adherent->getPostalCode();
        $dto->city = $adherent->getCity();
        $dto->cityName = $adherent->getCityName();
        $dto->country = $adherent->getCountry();
        $dto->nationality = $adherent->getNationality();

        return $dto;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount)
    {
        $this->amount = floor($amount * 100) / 100;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender)
    {
        $this->gender = $gender;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName)
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName)
    {
        $this->lastName = $lastName;
    }

    public function setEmailAddress(?string $emailAddress)
    {
        $this->emailAddress = mb_strtolower($emailAddress);
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address)
    {
        $this->address = $address;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city)
    {
        $this->city = $city;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName)
    {
        $this->cityName = $cityName;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country)
    {
        $this->country = $country;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function setClientIp(?string $clientIp): void
    {
        $this->clientIp = $clientIp;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): void
    {
        $this->duration = $duration ?? PayboxPaymentSubscription::NONE;
    }

    public function retryPayload(array $payload): self
    {
        $retry = clone $this;

        if (isset($payload['ge']) && \in_array($payload['ge'], Genders::ALL, true)) {
            $retry->gender = $payload['ge'];
        }

        if (isset($payload['ln'])) {
            $retry->lastName = (string) $payload['ln'];
        }

        if (isset($payload['fn'])) {
            $retry->firstName = (string) $payload['fn'];
        }

        if (isset($payload['em'])) {
            $retry->emailAddress = (string) $payload['em'];
        }

        if ($payload['co']) {
            $retry->country = (string) $payload['co'];
        }

        if ($payload['na']) {
            $retry->nationality = (string) $payload['na'];
        }

        if (isset($payload['pc'])) {
            $retry->postalCode = (string) $payload['pc'];
        }

        if (isset($payload['ci'])) {
            $retry->cityName = (string) $payload['ci'];
        }

        if (isset($payload['ad'])) {
            $retry->address = (string) $payload['ad'];
        }

        return $retry;
    }

    public function getConfirmDonationType(): ?string
    {
        return $this->confirmDonationType;
    }

    public function setConfirmDonationType(?string $confirmDonationType): void
    {
        $this->confirmDonationType = $confirmDonationType;
    }

    public function getConfirmSubscriptionAmount(): ?string
    {
        return $this->confirmSubscriptionAmount;
    }

    public function setConfirmSubscriptionAmount(?string $confirmSubscriptionAmount): void
    {
        $this->confirmSubscriptionAmount = $confirmSubscriptionAmount;
    }

    public function isSubscription(): bool
    {
        return PayboxPaymentSubscription::NONE !== $this->duration;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): void
    {
        $this->nationality = $nationality;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function forMembership()
    {
        $this->source = DonationSourceEnum::MEMBERSHIP;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getAdherentId(): ?int
    {
        return $this->adherentId;
    }

    public function updateFromAdherent(Adherent $adherent): void
    {
        $this->adherentId = $adherent->getId();
        $this->gender = $adherent->getGender();
        $this->firstName = $adherent->getFirstName();
        $this->lastName = $adherent->getLastName();
        $this->emailAddress = $adherent->getEmailAddress();
        $this->address = $adherent->getAddress();
        $this->postalCode = $adherent->getPostalCode();
        $this->city = $adherent->getCity();
        $this->cityName = $adherent->getCityName();
        $this->country = $adherent->getCountry();
        $this->nationality = $adherent->getNationality();
    }
}

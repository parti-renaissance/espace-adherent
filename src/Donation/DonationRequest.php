<?php

namespace AppBundle\Donation;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use AppBundle\Form\DonationRequestType;
use AppBundle\Validator\MaxFiscalYearDonation;
use AppBundle\Validator\MaxMonthDonation;
use AppBundle\Validator\PayboxSubscription as AssertPayboxSubscription;
use AppBundle\Validator\UniqueDonationSubscription;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use AppBundle\ValueObject\Genders;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueDonationSubscription
 * @MaxMonthDonation
 */
class DonationRequest
{
    public const DEFAULT_AMOUNT = 50.0;
    public const ALERT_AMOUNT = 200;

    private $uuid;

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(value=0, message="donation.amount.greater_than_0")
     * @MaxFiscalYearDonation
     */
    private $amount;

    /**
     * @Assert\NotBlank(message="common.gender.invalid_choice")
     * @Assert\Choice(
     *     callback={"AppBundle\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true
     * )
     */
    public $gender = Genders::FEMALE;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length"
     * )
     */
    public $firstName;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="common.last_name.min_length",
     *     maxMessage="common.last_name.max_length"
     * )
     */
    public $lastName;

    /**
     * @Assert\NotBlank
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $emailAddress;

    /**
     * @Assert\NotBlank(message="common.address.required")
     * @Assert\Length(max=150, maxMessage="common.address.max_length")
     */
    private $address;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=15)
     */
    private $postalCode;

    /**
     * @Assert\Length(max=15)
     */
    private $city;

    /**
     * @Assert\Length(max=255)
     */
    private $cityName;

    /**
     * @Assert\NotBlank
     * @AssertUnitedNationsCountry(message="common.country.invalid")
     */
    private $country;

    /**
     * @Assert\NotBlank
     * @Assert\Country(message="common.nationality.invalid")
     */
    private $nationality;

    /**
     * @Assert\Length(max=12)
     */
    private $code;

    private $clientIp;

    /**
     * @AssertPayboxSubscription
     */
    private $duration;

    /**
     * @Assert\Choice(DonationRequestType::CONFIRM_DONATION_TYPE_CHOICES)
     */
    private $confirmDonationType = DonationRequestType::CONFIRM_DONATION_TYPE_UNIQUE;

    /**
     * @Assert\Range(min=0, max=7500)
     */
    private $confirmSubscriptionAmount;

    private $type;

    public function __construct(
        UuidInterface $uuid,
        string $clientIp,
        float $amount = self::DEFAULT_AMOUNT,
        int $duration = PayboxPaymentSubscription::NONE,
        string $type = Donation::TYPE_CB
    ) {
        $this->uuid = $uuid;
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

    public function getClientIp(): string
    {
        return $this->clientIp;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
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

    public function setNationality(string $nationality): void
    {
        $this->nationality = $nationality;
    }

    public function getType(): string
    {
        return $this->type;
    }
}

<?php

namespace App\Donation\Request;

use App\Address\Address;
use App\Donation\DonationSourceEnum;
use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Form\Renaissance\Donation\DonationRequestConfirmType;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Renaissance\Donation\DonationRequestStateEnum;
use App\Validator\MaxFiscalYearDonation;
use App\Validator\MaxMonthDonation;
use App\Validator\PayboxSubscription as AssertPayboxSubscription;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\UniqueDonationSubscription;
use App\ValueObject\Genders;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueDonationSubscription(groups={"Default", "fill_personal_info"})
 * @MaxFiscalYearDonation(groups={"Default", "fill_personal_info"})
 * @MaxMonthDonation(groups={"Default", "choose_donation_amount"})
 * @AssertRecaptcha(api="friendly_captcha", groups={"donation_request_mentions"})
 */
class DonationRequest implements DonationRequestInterface, RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    public const DEFAULT_AMOUNT = 50.0;
    public const DEFAULT_AMOUNT_V2 = 60;
    public const DEFAULT_AMOUNT_SUBSCRIPTION_V2 = 10;

    public const MIN_AMOUNT = 10;
    public const MIN_AMOUNT_SUBSCRIPTION = 5;

    public const MAX_AMOUNT = 7500;
    public const MAX_AMOUNT_SUBSCRIPTION = 625;

    public const ALERT_AMOUNT = 200;

    private string $state = DonationRequestStateEnum::STATE_DONATION_AMOUNT;

    /**
     * @Assert\NotBlank(groups={"Default", "choose_donation_amount"})
     * @Assert\Expression(
     *     expression="(this.isSubscription() and value >= 5 and value <= 625) or (!this.isSubscription() and value >= 10 and value <= 7500)",
     *     message="donation.amount.invalid",
     *     groups={"Default", "choose_donation_amount"}
     * )
     */
    private $amount;

    /**
     * @Assert\NotBlank(message="common.gender.invalid_choice", groups={"Default", "fill_personal_info"})
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
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
    private $emailAddress = '';

    /**
     * @var Address
     *
     * @Assert\Valid
     */
    private $address;

    /**
     * @Assert\NotBlank(groups={"Default", "fill_personal_info"})
     * @Assert\Country(message="common.nationality.invalid", groups={"Default", "fill_personal_info"})
     * @Assert\Expression("this.getNationality() == 'FR' or this.getAddress().getCountry() == 'FR'", groups={"Default"}, message="donation.french_address_or_nationality_donation")
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

    public $localDestination = false;

    /**
     * @Assert\Choice(DonationRequestConfirmType::CONFIRM_DONATION_TYPE_CHOICES, groups={"donation_confirm_type"})
     */
    private $confirmDonationType = DonationRequestConfirmType::CONFIRM_DONATION_TYPE_UNIQUE;

    /**
     * @Assert\Range(min=0, max=7500, groups={"donation_confirm_type"})
     */
    private $confirmSubscriptionAmount;

    private ?\DateTime $donatedAt = null;

    private $type;

    private ?string $source = null;

    private ?int $adherentId = null;

    public function __construct(
        ?string $clientIp = null,
        float $amount = self::DEFAULT_AMOUNT,
        int $duration = PayboxPaymentSubscription::NONE,
        string $type = Donation::TYPE_CB
    ) {
        $this->clientIp = $clientIp;
        $this->address = new Address();
        $this->setAmount($amount);
        $this->duration = $duration;
        $this->type = $type;
    }

    public static function createFromAdherent(
        Adherent $adherent,
        ?string $clientIp = null,
        float $amount = self::DEFAULT_AMOUNT,
        int $duration = PayboxPaymentSubscription::NONE,
        string $type = Donation::TYPE_CB
    ): self {
        $dto = new self($clientIp, $amount, $duration, $type);
        $dto->gender = $adherent->getGender();
        $dto->firstName = $adherent->getFirstName();
        $dto->lastName = $adherent->getLastName();
        $dto->emailAddress = $adherent->getEmailAddress();
        $dto->address = Address::createFromAddress($adherent->getPostAddress());
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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount)
    {
        $this->amount = floor($amount * 100) / 100;
    }

    public function isLocalDestination(): bool
    {
        return (bool) $this->localDestination;
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

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
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
            $retry->address->setCountry((string) $payload['co']);
        }

        if ($payload['na']) {
            $retry->nationality = (string) $payload['na'];
        }

        if (isset($payload['pc'])) {
            $retry->address->setPostalCode((string) $payload['pc']);
        }

        if (isset($payload['ci'])) {
            $retry->address->setCityName((string) $payload['ci']);
        }

        if (isset($payload['ad'])) {
            $retry->address->setAddress((string) $payload['ad']);
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

    public function forMembership(): void
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

    public function getDonatedAt(): ?\DateTime
    {
        return $this->donatedAt;
    }

    public function setDonatedAt(?\DateTime $donatedAt): void
    {
        $this->donatedAt = $donatedAt;
    }

    public function updateFromAdherent(Adherent $adherent): void
    {
        $this->adherentId = $adherent->getId();
        $this->gender = $adherent->getGender();
        $this->firstName = $adherent->getFirstName();
        $this->lastName = $adherent->getLastName();
        $this->emailAddress = $adherent->getEmailAddress();
        $this->address = Address::createFromAddress($adherent->getPostAddress());
        $this->nationality = $adherent->getNationality();
    }
}

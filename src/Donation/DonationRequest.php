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
use App\ValueObject\Genders;
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
     * @var Address
     *
     * @Assert\Valid
     */
    private $address;

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
        string $clientIp = null,
        float $amount = self::DEFAULT_AMOUNT,
        int $duration = PayboxPaymentSubscription::NONE,
        string $type = Donation::TYPE_CB
    ) {
        $this->clientIp = $clientIp;
        $this->emailAddress = '';
        $this->address = new Address();
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
        $dto = new self($clientIp, $amount, $duration);
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
        $this->address = Address::createFromAddress($adherent->getPostAddress());
        $this->nationality = $adherent->getNationality();
    }
}

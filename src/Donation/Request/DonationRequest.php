<?php

namespace App\Donation\Request;

use App\Address\Address;
use App\Donation\DonationSourceEnum;
use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\MaxFiscalYearDonation;
use App\Validator\MaxMonthDonation;
use App\Validator\PayboxSubscription as AssertPayboxSubscription;
use App\Validator\UniqueDonationSubscription;
use App\ValueObject\Genders;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MaxFiscalYearDonation(groups={"Default", "fill_personal_info"})
 * @MaxMonthDonation(groups={"Default", "choose_donation_amount"})
 */
#[UniqueDonationSubscription(groups: ['Default', 'fill_personal_info'])]
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

    #[Assert\NotBlank(groups: ['Default', 'choose_donation_amount'])]
    #[Assert\Expression(expression: '(this.isSubscription() and value >= 5 and value <= 625) or (!this.isSubscription() and value >= 10 and value <= 7500)', message: 'donation.amount.invalid', groups: ['Default', 'choose_donation_amount'])]
    private $amount;

    #[Assert\NotBlank(message: 'common.gender.invalid_choice', groups: ['Default', 'fill_personal_info'])]
    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.gender.invalid_choice', groups: ['Default', 'fill_personal_info'])]
    public $gender;

    #[Assert\NotBlank(groups: ['Default', 'fill_personal_info'])]
    #[Assert\Length(min: 2, max: 50, minMessage: 'common.first_name.min_length', maxMessage: 'common.first_name.max_length', groups: ['Default', 'fill_personal_info'])]
    public $firstName;

    #[Assert\NotBlank(groups: ['Default', 'fill_personal_info'])]
    #[Assert\Length(min: 1, max: 50, minMessage: 'common.last_name.min_length', maxMessage: 'common.last_name.max_length', groups: ['Default', 'fill_personal_info'])]
    public $lastName;

    #[Assert\NotBlank(groups: ['Default', 'fill_personal_info'])]
    #[Assert\Email(message: 'common.email.invalid', groups: ['Default', 'fill_personal_info'])]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length', groups: ['Default', 'fill_personal_info'])]
    private $emailAddress = '';

    /**
     * @var Address
     */
    #[Assert\Valid]
    private $address;

    #[Assert\NotBlank(groups: ['Default', 'fill_personal_info'])]
    #[Assert\Country(message: 'common.nationality.invalid', groups: ['Default', 'fill_personal_info'])]
    #[Assert\Expression("this.getNationality() == 'FR' or this.getAddress().getCountry() == 'FR'", groups: ['Default'], message: 'donation.french_address_or_nationality_donation')]
    private $nationality;

    #[Assert\Regex(pattern: '/^[\d]{6}$/', message: 'donation.code.invalid')]
    private $code;

    private $clientIp;

    /**
     * @AssertPayboxSubscription(groups={"Default", "choose_donation_amount"})
     */
    private $duration;

    public $localDestination = false;

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
}

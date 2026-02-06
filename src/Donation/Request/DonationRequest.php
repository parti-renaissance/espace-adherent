<?php

declare(strict_types=1);

namespace App\Donation\Request;

use App\Address\Address;
use App\Donation\DonationSourceEnum;
use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Utils\UtmParams;
use App\Validator\MaxFiscalYearDonation;
use App\Validator\MaxMonthDonation;
use App\Validator\PayboxSubscription as AssertPayboxSubscription;
use App\Validator\UniqueDonationSubscription;
use App\ValueObject\Genders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

#[MaxFiscalYearDonation(groups: ['Default', 'fill_personal_info'])]
#[MaxMonthDonation(groups: ['Default', 'choose_donation_amount'])]
#[UniqueDonationSubscription(groups: ['Default', 'fill_personal_info'])]
class DonationRequest implements DonationRequestInterface, RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    public const DEFAULT_AMOUNT = 60;
    public const DEFAULT_AMOUNT_SUBSCRIPTION = 10;

    public const MIN_AMOUNT = 10;
    public const MIN_AMOUNT_SUBSCRIPTION = 5;

    public const MAX_AMOUNT = 7500;
    public const MAX_AMOUNT_SUBSCRIPTION = 625;

    #[Assert\Expression(expression: '(this.isSubscription() and value >= 5 and value <= 625) or (!this.isSubscription() and value >= 10 and value <= 7500)', message: 'donation.amount.invalid', groups: ['Default', 'choose_donation_amount'])]
    #[Assert\NotBlank(groups: ['Default', 'choose_donation_amount'])]
    private $amount;

    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.gender.invalid_choice', groups: ['Default', 'fill_personal_info'])]
    #[Assert\NotBlank(message: 'common.gender.invalid_choice', groups: ['Default', 'fill_personal_info'])]
    public $gender;

    #[Assert\Length(min: 2, max: 50, minMessage: 'common.first_name.min_length', maxMessage: 'common.first_name.max_length', groups: ['Default', 'fill_personal_info'])]
    #[Assert\NotBlank(groups: ['Default', 'fill_personal_info'])]
    public $firstName;

    #[Assert\Length(min: 1, max: 50, minMessage: 'common.last_name.min_length', maxMessage: 'common.last_name.max_length', groups: ['Default', 'fill_personal_info'])]
    #[Assert\NotBlank(groups: ['Default', 'fill_personal_info'])]
    public $lastName;

    #[Assert\Email(message: 'common.email.invalid', groups: ['Default', 'fill_personal_info'])]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length', groups: ['Default', 'fill_personal_info'])]
    #[Assert\NotBlank(groups: ['Default', 'fill_personal_info'])]
    private $emailAddress = '';

    /**
     * @var Address
     */
    #[Assert\Valid]
    private $address;

    #[Assert\Country(message: 'common.nationality.invalid', groups: ['Default', 'fill_personal_info'])]
    #[Assert\Expression("this.getNationality() == 'FR' or this.getAddress().getCountry() == 'FR'", groups: ['Default'], message: 'donation.french_address_or_nationality_donation')]
    #[Assert\NotBlank(groups: ['Default', 'fill_personal_info'])]
    private $nationality;

    #[Assert\Regex(pattern: '/^[\d]{6}$/', message: 'donation.code.invalid')]
    private $code;

    private $clientIp;

    #[AssertPayboxSubscription(groups: ['Default', 'choose_donation_amount'])]
    private $duration;

    public $localDestination = false;

    private ?\DateTime $donatedAt = null;

    private $type;

    private ?string $source = null;

    public ?string $utmSource = null;
    public ?string $utmCampaign = null;

    public function __construct(
        float $amount,
        int $duration = PayboxPaymentSubscription::NONE,
        string $type = Donation::TYPE_CB,
        ?string $clientIp = null,
    ) {
        $this->clientIp = $clientIp;
        $this->address = new Address();
        $this->setAmount($amount);
        $this->duration = $duration;
        $this->type = $type;
    }

    public static function create(
        ?Request $httpRequest,
        float $amount,
        int $duration,
        ?Adherent $adherent = null,
        string $type = Donation::TYPE_CB,
    ): self {
        $dto = new self($amount, $duration, $type, $httpRequest?->getClientIp());

        if ($adherent) {
            $dto->gender = $adherent->getGender();
            $dto->firstName = $adherent->getFirstName();
            $dto->lastName = $adherent->getLastName();
            $dto->emailAddress = $adherent->getEmailAddress();
            $dto->address = Address::createFromAddress($adherent->getPostAddress());
            $dto->nationality = $adherent->getNationality();
        }

        if ($httpRequest?->query->has(UtmParams::UTM_SOURCE)) {
            $dto->utmSource = UtmParams::filterUtmParameter($httpRequest->query->get(UtmParams::UTM_SOURCE));
        }
        if ($httpRequest?->query->has(UtmParams::UTM_CAMPAIGN)) {
            $dto->utmCampaign = UtmParams::filterUtmParameter($httpRequest->query->get(UtmParams::UTM_CAMPAIGN));
        }

        return $dto;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): void
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

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress ? mb_strtolower($emailAddress) : null;
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

    public function getDonatedAt(): ?\DateTime
    {
        return $this->donatedAt;
    }

    public function setDonatedAt(?\DateTime $donatedAt): void
    {
        $this->donatedAt = $donatedAt;
    }
}

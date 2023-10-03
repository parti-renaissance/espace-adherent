<?php

namespace App\Membership\MembershipRequest;

use App\Address\Address;
use App\Donation\Request\DonationRequestInterface;
use App\Entity\Adherent;
use App\Membership\MembershipSourceEnum;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Renaissance\Membership\MembershipRequestStateEnum;
use App\Validator\BannedAdherent;
use App\Validator\MaxFiscalYearDonation;
use App\Validator\Recaptcha as AssertRecaptcha;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertRecaptcha(api="friendly_captcha", groups={"fill_personal_info"})
 * @MaxFiscalYearDonation(groups={"membership_request_amount"})
 */
class RenaissanceMembershipRequest extends AbstractMembershipRequest implements RecaptchaChallengeInterface, DonationRequestInterface
{
    use RecaptchaChallengeTrait;

    public const EMAIL = 'email';
    public const UTM_SOURCE = 'utm_source';
    public const UTM_CAMPAIGN = 'utm_campaign';

    private string $state = MembershipRequestStateEnum::STATE_START;

    /**
     * @Assert\NotBlank(groups={"membership_request_amount"})
     * @Assert\GreaterThanOrEqual(value=10, message="Le montant de la cotisation est invalid", groups={"membership_request_amount"})
     */
    public ?float $amount = null;

    /**
     * @Assert\NotBlank(groups={"fill_personal_info"})
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length",
     *     groups={"fill_personal_info"}
     * )
     */
    public ?string $firstName = null;

    /**
     * @Assert\NotBlank(groups={"fill_personal_info"})
     * @Assert\Length(
     *     min=1,
     *     max=50,
     *     minMessage="common.last_name.min_length",
     *     maxMessage="common.last_name.max_length",
     *     groups={"fill_personal_info"}
     * )
     */
    public ?string $lastName = null;

    /**
     * @Assert\Valid(groups={"fill_personal_info"})
     */
    private Address $address;

    /**
     * @Assert\Expression("this.getAdherentId() or this.password", groups="fill_personal_info")
     * @Assert\Length(allowEmptyString=true, min=8, minMessage="adherent.plain_password.min_length", groups={"fill_personal_info"})
     */
    public ?string $password = null;

    /**
     * @Assert\IsTrue(message="common.conditions.not_accepted", groups={"membership_request_amount"})
     */
    public bool $conditions = false;

    /**
     * @Assert\NotBlank(groups={"fill_personal_info"})
     * @Assert\Email(message="common.email.invalid", groups={"fill_personal_info"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"fill_personal_info"})
     * @BannedAdherent(groups={"fill_personal_info"})
     */
    protected ?string $emailAddress = null;

    private ?int $adherentId = null;
    private bool $isCertified = false;

    public ?string $utmSource = null;
    public ?string $utmCampaign = null;
    public bool $emailFromRequest = false;

    public function __construct()
    {
        $this->address = new Address();
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

    public function setAmount(float $amount): void
    {
        $this->amount = floor($amount * 100) / 100;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function getAdherentId(): ?int
    {
        return $this->adherentId;
    }

    public function isCertified(): bool
    {
        return $this->isCertified;
    }

    final public function getSource(): string
    {
        return MembershipSourceEnum::RENAISSANCE;
    }

    public function updateFromAdherent(Adherent $adherent): void
    {
        $this->adherentId = $adherent->getId();
        $this->isCertified = $adherent->isCertified();
        $this->emailAddress = $adherent->getEmailAddress();
        $this->firstName = $adherent->getFirstName();
        $this->lastName = $adherent->getLastName();
        $this->address = Address::createFromAddress($adherent->getPostAddress());
    }
}

<?php

namespace App\Renaissance\Membership;

use App\Address\Address;
use App\Donation\DonationRequestInterface;
use App\Membership\MembershipRequest\AbstractMembershipRequest;
use App\Membership\MembershipRequest\MembershipCustomGenderInterface;
use App\Membership\MembershipSourceEnum;
use App\Validator\BannedAdherent;
use App\Validator\CustomGender as AssertCustomGender;
use App\Validator\MaxFiscalYearDonation;
use App\Validator\UniqueMembership as AssertUniqueMembership;
use App\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueMembership(groups={"fill_personal_info"})
 * @AssertCustomGender(groups={"fill_personal_info"})
 */
class MembershipRequestCommand extends AbstractMembershipRequest implements MembershipCustomGenderInterface, DonationRequestInterface
{
    private string $state = MembershipRequestCommandStateEnum::STATE_INITIALIZE;

    /**
     * @Assert\NotBlank(groups={"membership_request_amount"})
     * @Assert\GreaterThan(value=0, message="donation.amount.greater_than_0", groups={"membership_request_amount"})
     * @MaxFiscalYearDonation(groups={"membership_request_amount"})
     */
    private ?float $amount = null;

    /**
     * @Assert\NotBlank(message="common.gender.not_blank", groups={"fill_personal_info"})
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true,
     *     groups={"fill_personal_info"}
     * )
     */
    public ?string $gender = null;

    public ?string $customGender = null;

    /**
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     allowEmptyString=false,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length",
     *     groups={"fill_personal_info"}
     * )
     */
    public ?string $firstName = null;

    /**
     * @var string
     *
     * @Assert\Length(
     *     min=1,
     *     max=50,
     *     allowEmptyString=false,
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
     * @Assert\NotBlank(groups="Registration")
     * @Assert\Length(min=8, minMessage="adherent.plain_password.min_length", groups={"fill_personal_info"})
     */
    public string $password;

    /**
     * @var bool
     *
     * @Assert\IsTrue(message="common.conditions.not_accepted", groups={"membership_request_mentions"})
     */
    public $conditions;

    /**
     * @Assert\NotBlank(groups={"fill_personal_info"})
     * @Assert\Country(message="common.nationality.invalid")
     */
    public ?string $nationality = null;

    /**
     * @Assert\NotBlank(groups={"fill_personal_info"})
     * @Assert\Email(message="common.email.invalid", groups={"fill_personal_info"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"fill_personal_info"})
     * @BannedAdherent(groups={"fill_personal_info"})
     */
    protected string $emailAddress = '';

    /**
     * @AssertPhoneNumber(defaultRegion="FR", groups={"fill_personal_info"})
     */
    private ?PhoneNumber $phone = null;

    /**
     * @Assert\NotBlank(message="adherent.birthdate.not_blank", groups={"fill_personal_info"})
     * @Assert\Range(max="-15 years", maxMessage="adherent.birthdate.minimum_required_age", groups={"fill_personal_info"})
     */
    private ?\DateTimeInterface $birthdate = null;

    private ?string $clientIp = null;

    private ?int $predefinedAmount = null;

    private ?float $otherAmount = null;

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

    public function setAmount(?float $amount): void
    {
        $this->amount = floor($amount * 100) / 100;
    }

    public function getPredefinedAmount(): ?int
    {
        return $this->predefinedAmount;
    }

    public function setPredefinedAmount(?int $predefinedAmount): void
    {
        $this->predefinedAmount = $predefinedAmount;
    }

    public function getOtherAmount(): ?float
    {
        return $this->otherAmount;
    }

    public function setOtherAmount(?float $otherAmount): void
    {
        $this->otherAmount = $otherAmount;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getGenderName(): ?string
    {
        return array_search($this->gender, Genders::CHOICES, true);
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = mb_strtolower($emailAddress);
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress ?: '';
    }

    public function setPhone(?PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function setClientIp(?string $clientIp): void
    {
        $this->clientIp = $clientIp;
    }

    final public function getSource(): string
    {
        return MembershipSourceEnum::RENAISSANCE;
    }

    public function isFillPersonalInfo(): bool
    {
        return MembershipRequestCommandStateEnum::STATE_PERSONAL_INFO === $this->state;
    }

    public function isChooseAmount(): bool
    {
        return MembershipRequestCommandStateEnum::STATE_ADHESION_AMOUNT === $this->state;
    }

    public function isTermsAndConditions(): bool
    {
        return MembershipRequestCommandStateEnum::STATE_TERMS_AND_CONDITIONS === $this->state;
    }

    public function isSummary(): bool
    {
        return MembershipRequestCommandStateEnum::STATE_SUMMARY === $this->state;
    }

    public function isPayment(): bool
    {
        return MembershipRequestCommandStateEnum::STATE_ADHESION_PAYMENT === $this->state;
    }

    public function isFinish(): bool
    {
        return MembershipRequestCommandStateEnum::STATE_FINISH === $this->state;
    }
}

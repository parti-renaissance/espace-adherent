<?php

namespace App\Membership\MembershipRequest;

use App\Address\Address;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\BannedAdherent;
use App\Validator\CustomGender as AssertCustomGender;
use App\Validator\MaxFiscalYearDonation;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\UniqueMembership as AssertUniqueMembership;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueMembership(groups={"Registration", "Update"})
 * @AssertCustomGender(groups={"Registration", "Update"})
 * @AssertRecaptcha(groups={"Registration"})
 */
class RenaissanceMembershipRequest extends AbstractMembershipRequest implements RecaptchaChallengeInterface, MembershipCustomGenderInterface
{
    use RecaptchaChallengeTrait;

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(value=0, message="donation.amount.greater_than_0")
//     * @MaxFiscalYearDonation
     */
    private ?float $amount = null;

    /**
     * @Assert\NotBlank(message="common.gender.not_blank", groups={"Update"})
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true,
     *     groups={"Update"}
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
     *     groups={"Registration", "Update"}
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
     *     groups={"Registration", "Update"}
     * )
     */
    public ?string $lastName = null;

    /**
     * @Assert\Valid
     */
    private Address $address;

    /**
     * @Assert\Choice(
     *     callback={"App\Membership\ActivityPositionsEnum", "all"},
     *     message="adherent.activity_position.invalid_choice",
     *     strict=true,
     *     groups={"Update"}
     * )
     */
    public ?string $position = null;

    /**
     * @Assert\NotBlank(groups="Registration")
     * @Assert\Length(min=8, minMessage="adherent.plain_password.min_length", groups={"Registration"})
     */
    public string $password;

    /**
     * @var bool
     *
     * @Assert\IsTrue(message="common.conditions.not_accepted", groups={"Conditions"})
     */
    public $conditions;

    /**
     * @Assert\NotBlank(groups={"Registration", "Update"})
     * @Assert\Country(message="common.nationality.invalid")
     */
    public ?string $nationality = null;

    /**
     * @Assert\NotBlank(groups={"Registration", "Update"})
     * @Assert\Email(message="common.email.invalid", groups={"Registration", "Update"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"Registration", "Update"})
     * @BannedAdherent(groups={"Registration"})
     */
    protected string $emailAddress = '';

    /**
     * @AssertPhoneNumber(defaultRegion="FR", groups={"Update"})
     */
    private ?PhoneNumber $phone = null;

    /**
     * @Assert\NotBlank(message="adherent.birthdate.not_blank", groups={"Update"})
     * @Assert\Range(max="-15 years", maxMessage="adherent.birthdate.minimum_required_age", groups={"Update"})
     */
    private ?\DateTimeInterface $birthdate = null;

    private bool $asUser;

    private bool $certified = false;

    private ?string $clientIp = null;

    public function __construct(bool $asUser = false)
    {
        $this->asUser = $asUser;
        $this->address = new Address();
    }

    public static function createWithCaptcha(
        ?string $countryIso,
        string $recaptchaAnswer = null,
        bool $asUser = false
    ): self {
        $dto = new self($asUser);
        $dto->setRecaptcha($recaptchaAnswer);

        if ($countryIso) {
            $dto->address->setCountry($countryIso);
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

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
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

    public function isAsUser(): bool
    {
        return $this->asUser;
    }

    public function isCertified(): bool
    {
        return $this->certified;
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function setClientIp(?string $clientIp): void
    {
        $this->clientIp = $clientIp;
    }

    public function getSource(): ?string
    {
        return null;
    }
}

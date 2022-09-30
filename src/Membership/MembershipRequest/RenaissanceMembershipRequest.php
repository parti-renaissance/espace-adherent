<?php

namespace App\Membership\MembershipRequest;

use App\Address\Address;
use App\Donation\DonationRequestInterface;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Membership\MembershipSourceEnum;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Renaissance\Membership\MembershipRequestStateEnum;
use App\Validator\BannedAdherent;
use App\Validator\MaxFiscalYearDonation;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\UniqueMembership as AssertUniqueMembership;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueMembership(groups={"fill_personal_info"})
 * @AssertRecaptcha(groups={"fill_personal_info"})
 * @MaxFiscalYearDonation(groups={"membership_request_amount"})
 */
class RenaissanceMembershipRequest extends AbstractMembershipRequest implements RecaptchaChallengeInterface, MembershipCustomGenderInterface, DonationRequestInterface
{
    use RecaptchaChallengeTrait;

    private string $state = MembershipRequestStateEnum::STATE_START;

    /**
     * @Assert\NotBlank(groups={"membership_request_amount"})
     * @Assert\GreaterThanOrEqual(value=1, message="donation.amount.greater_than_1", groups={"membership_request_amount"})
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
    private ?string $gender = null;

    private ?string $customGender = null;

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
     * @Assert\Choice(
     *     callback={"App\Membership\ActivityPositionsEnum", "all"},
     *     message="adherent.activity_position.invalid_choice",
     *     strict=true,
     *     groups={"membership_request_additional_informations"}
     * )
     */
    public ?string $position = null;

    /**
     * @Assert\Expression("this.getAdherentId() or this.password", groups="fill_personal_info")
     * @Assert\Length(min=8, minMessage="adherent.plain_password.min_length", groups={"fill_personal_info"})
     */
    public ?string $password = null;

    /**
     * @Assert\NotBlank(groups={"membership_request_mentions"})
     * @Assert\IsTrue(message="common.conditions.not_accepted", groups={"membership_request_mentions"})
     */
    public ?bool $conditions = null;

    /**
     * @Assert\Expression(
     *     expression="!(value == false and this.territoireProgresMembership == false and this.agirMembership == false)",
     *     message="adherent.exclusive_membership.no_accepted",
     *     groups={"membership_request_additional_informations"}
     * )
     */
    public bool $exclusiveMembership = false;

    public bool $territoireProgresMembership = false;

    public bool $agirMembership = false;

    /**
     * @Assert\NotBlank(groups={"fill_personal_info"})
     * @Assert\Country(message="common.nationality.invalid", groups={"fill_personal_info"})
     */
    public ?string $nationality = null;

    /**
     * @Assert\NotBlank(groups={"fill_personal_info"})
     * @Assert\Email(message="common.email.invalid", groups={"fill_personal_info"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"fill_personal_info"})
     * @BannedAdherent(groups={"fill_personal_info"})
     */
    protected ?string $emailAddress = null;

    /**
     * @AssertPhoneNumber(defaultRegion="FR", groups={"membership_request_additional_informations"})
     */
    private ?PhoneNumber $phone = null;

    /**
     * @Assert\NotBlank(message="adherent.birthdate.not_blank", groups={"fill_personal_info"})
     * @Assert\Range(max="-15 years", maxMessage="adherent.birthdate.minimum_required_age", groups={"fill_personal_info"})
     */
    private ?\DateTimeInterface $birthdate = null;

    /**
     * @Assert\Type(Zone::class)
     */
    private ?Zone $activismZone = null;

    private ?string $clientIp = null;
    private ?int $adherentId = null;
    private bool $isCertified = false;

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

    public static function createWithCaptcha(?string $countryIso, string $recaptchaAnswer = null): self
    {
        $dto = new self();
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

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getCustomGender(): ?string
    {
        return $this->customGender;
    }

    public function setCustomGender(?string $customGender): void
    {
        $this->customGender = $customGender;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): Address
    {
        return $this->address;
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

    public function setActivismZone(?Zone $zone): void
    {
        $this->activismZone = $zone;
    }

    public function getActivismZone(): ?Zone
    {
        return $this->activismZone;
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function setClientIp(?string $clientIp): void
    {
        $this->clientIp = $clientIp;
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
        $this->birthdate = $adherent->getBirthdate();
        $this->gender = $adherent->getGender();
        $this->customGender = $adherent->getCustomGender();
        $this->nationality = $adherent->getNationality();
        $this->phone = $adherent->getPhone();
        $this->address = Address::createFromAddress($adherent->getPostAddress());
        $this->activismZone = $adherent->getActivismZone();
        $this->exclusiveMembership = $adherent->isExclusiveMembership();
        $this->territoireProgresMembership = $adherent->isTerritoireProgresMembership();
        $this->agirMembership = $adherent->isAgirMembership();
    }
}

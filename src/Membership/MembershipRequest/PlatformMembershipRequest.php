<?php

namespace App\Membership\MembershipRequest;

use App\Address\Address;
use App\Entity\Adherent;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\BannedAdherent;
use App\Validator\CustomGender as AssertCustomGender;
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
class PlatformMembershipRequest extends AbstractMembershipRequest implements RecaptchaChallengeInterface, MembershipCustomGenderInterface
{
    use RecaptchaChallengeTrait;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="common.gender.not_blank", groups={"Update"})
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     groups={"Update"}
     * )
     */
    public $gender;

    /**
     * @var string|null
     */
    public $customGender;

    /**
     * @Assert\NotBlank(groups={"Registration", "Update"})
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     allowEmptyString=true,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length",
     *     groups={"Registration", "Update"}
     * )
     */
    public ?string $firstName = null;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"Registration", "Update"})
     * @Assert\Length(
     *     min=1,
     *     max=50,
     *     allowEmptyString=true,
     *     minMessage="common.last_name.min_length",
     *     maxMessage="common.last_name.max_length",
     *     groups={"Registration", "Update"}
     * )
     */
    public $lastName;

    /**
     * @var Address
     *
     * @Assert\Valid
     */
    private $address;

    /**
     * @var string|null
     *
     * @Assert\Choice(
     *     callback={"App\Membership\ActivityPositionsEnum", "all"},
     *     message="adherent.activity_position.invalid_choice",
     *     groups={"Update"}
     * )
     */
    public $position;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups="Registration")
     * @Assert\Length(allowEmptyString=true, min=8, minMessage="adherent.plain_password.min_length", groups={"Registration"})
     */
    public $password;

    /**
     * @var bool
     *
     * @Assert\IsTrue(message="common.conditions.not_accepted", groups={"Conditions"})
     */
    public $conditions;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(groups={"Registration", "Update"})
     * @Assert\Country(message="common.nationality.invalid")
     */
    public $nationality;

    /**
     * @Assert\NotBlank(groups={"Registration", "Update"})
     * @Assert\Email(message="common.email.invalid", groups={"Registration", "Update"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"Registration", "Update"})
     * @BannedAdherent(groups={"Registration"})
     */
    protected ?string $emailAddress = null;

    /**
     * @var PhoneNumber|null
     *
     * @AssertPhoneNumber(options={"groups": {"Update"}})
     */
    private $phone;

    /**
     * @var \DateTime|null
     *
     * @Assert\NotBlank(message="adherent.birthdate.not_blank", groups={"Update"})
     * @Assert\Range(max="-15 years", maxMessage="adherent.birthdate.minimum_required_age", groups={"Update"})
     */
    private $birthdate;

    /**
     * @var bool
     */
    private $elected = false;

    /**
     * @var array
     *
     * @Assert\Choice(callback={"App\Membership\MandatesEnum", "all"})
     */
    private $mandates;

    /**
     * @var bool
     */
    private $certified = false;

    private bool $asUser;

    public function __construct(bool $asUser = false)
    {
        $this->asUser = $asUser;
        $this->address = new Address();
    }

    public static function createWithCaptcha(
        ?string $countryIso,
        ?string $recaptchaAnswer = null,
        bool $asUser = false
    ): self {
        $dto = new self($asUser);
        $dto->setRecaptcha($recaptchaAnswer);

        if ($countryIso) {
            $dto->address->setCountry($countryIso);
        }

        return $dto;
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        $dto = new self();
        $dto->customGender = $adherent->getCustomGender();
        $dto->gender = $adherent->getGender();
        $dto->firstName = $adherent->getFirstName();
        $dto->lastName = $adherent->getLastName();
        $dto->birthdate = $adherent->getBirthdate();
        $dto->position = $adherent->getPosition();
        $dto->address = Address::createFromAddress($adherent->getPostAddress());
        $dto->phone = $adherent->getPhone();
        $dto->emailAddress = $adherent->getEmailAddress();
        $dto->mandates = $adherent->getMandates();
        $dto->elected = $adherent->hasMandate();
        $dto->nationality = $adherent->getNationality();
        $dto->certified = $adherent->isCertified();

        return $dto;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getCustomGender(): ?string
    {
        return $this->customGender;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function isCertified(): bool
    {
        return $this->certified;
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

    public function setBirthdate(?\DateTime $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function isElected(): bool
    {
        return $this->elected;
    }

    public function setElected(bool $elected): void
    {
        $this->elected = $elected;
    }

    public function getMandates(): ?array
    {
        return $this->mandates;
    }

    public function setMandates(?array $mandates): void
    {
        $this->mandates = $mandates;
    }

    final public function getSource(): ?string
    {
        return null;
    }

    public function isAsUser(): bool
    {
        return $this->asUser;
    }
}

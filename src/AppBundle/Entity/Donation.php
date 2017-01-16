<?php

namespace AppBundle\Entity;

use AppBundle\Exception\InitializedEntityException;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use AppBundle\Validator\FrenchPostalCode as AssertFrenchPostalCode;
use AppBundle\Validator\FrenchCity as AssertFrenchCity;
use AppBundle\Validator\CityAssociatedToPostalCode as AssertCityAssociatedToPostalCode;

/**
 * @ORM\Table(name="donations")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DonationRepository")
 *
 * @AssertCityAssociatedToPostalCode(postalCodeField="postalCode", cityField="city", message="common.city.invalid_postal_code")
 */
class Donation
{
    /**
     * @var UuidInterface|null
     *
     * @ORM\Column(type="uuid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank(message="donation.amount.not_blank")
     * @Assert\GreaterThan(value=0, message="donation.amount.greater_than_0")
     * @Assert\LessThanOrEqual(value=7500, message="donation.amount.less_than_7500")
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     *
     * @Assert\Choice(
     *   callback={"AppBundle\ValueObject\Genders", "all"},
     *   message="common.gender.invalid_choice",
     *   strict=true
     * )
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="common.first_name.not_blank")
     * @Assert\Length(
     *   min=2,
     *   max=50,
     *   minMessage="common.first_name.min_length",
     *   maxMessage="common.first_name.max_length"
     * )
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="common.last_name.not_blank")
     * @Assert\Length(
     *   min=2,
     *   max=50,
     *   minMessage="common.last_name.min_length",
     *   maxMessage="common.last_name.max_length"
     * )
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank(message="common.email.not_blank")
     * @Assert\Email(message="common.email.invalid")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     *
     * @Assert\NotBlank(message="common.country.not_blank")
     * @AssertUnitedNationsCountry(message="common.country.invalid")
     */
    private $country;

    /**
     * @var string|null
     *
     * @ORM\Column(length=11, nullable=true)
     *
     * @AssertFrenchPostalCode(message="common.postal_code.invalid")
     */
    private $postalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(length=20, nullable=true)
     *
     * @AssertFrenchCity(message="common.city.invalid")
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(length=150, nullable=true)
     *
     * @Assert\Length(max=150, maxMessage="common.address.max_length")
     */
    private $address;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber(defaultRegion="FR", message="common.phone_number.invalid")
     */
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, nullable=true)
     */
    private $payboxResultCode;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, nullable=true)
     */
    private $payboxAuthorizationCode;

    /**
     * @var array|null
     *
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $payboxPayload;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $finished;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     */
    private $clientIp;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $donatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->country = 'FR';
        $this->finished = false;

        $this->phone = new PhoneNumber();
        $this->phone->setCountryCode(33);
    }

    public function isSuccessful(): bool
    {
        return $this->finished && $this->donatedAt instanceof \DateTime;
    }

    /**
     * @return UuidInterface|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string|null $gender
     *
     * @return $this
     */
    public function setGender($gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     *
     * @return $this
     */
    public function setLastName($lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     *
     * @return $this
     */
    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     *
     * @return $this
     */
    public function setAddress($address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string|null $postalCode
     *
     * @return $this
     */
    public function setPostalCode($postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     *
     * @return $this
     */
    public function setCity($city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     *
     * @return $this
     */
    public function setCountry($country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return PhoneNumber|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param PhoneNumber|null $phone
     *
     * @return $this
     */
    public function setPhone($phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPayboxResultCode()
    {
        return $this->payboxResultCode;
    }

    public function setPayboxResultCode(string $payboxResultCode)
    {
        $this->payboxResultCode = $payboxResultCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPayboxAuthorizationCode()
    {
        return $this->payboxAuthorizationCode;
    }

    public function setPayboxAuthorizationCode(string $payboxAuthorizationCode)
    {
        $this->payboxAuthorizationCode = $payboxAuthorizationCode;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getPayboxPayload()
    {
        return $this->payboxPayload;
    }

    public function setPayboxPayload(array $payboxPayload): self
    {
        $this->payboxPayload = $payboxPayload;

        return $this;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): self
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    public function setClientIp(string $clientIp): self
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDonatedAt()
    {
        return $this->donatedAt;
    }

    public function setDonatedAt(\DateTime $donatedAt): self
    {
        $this->donatedAt = $donatedAt;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get finished.
     *
     * @return bool
     */
    public function getFinished()
    {
        return $this->finished;
    }

    public function init(string $clientIp)
    {
        if (null !== $this->id) {
            throw new InitializedEntityException($this);
        }

        $this->id = Uuid::uuid4();
        $this->clientIp = $clientIp;
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        $donation = new self();
        $donation->gender = $adherent->getGender();
        $donation->firstName = $adherent->getFirstName();
        $donation->lastName = $adherent->getFirstName();
        $donation->lastName = $adherent->getLastName();
        $donation->email = $adherent->getEmailAddress();
        $donation->address = $adherent->getAddress();
        $donation->postalCode = $adherent->getPostalCode();
        $donation->city = $adherent->getCity();
        $donation->phone = $adherent->getPhone();

        return $donation;
    }
}

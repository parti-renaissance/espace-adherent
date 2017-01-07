<?php

namespace AppBundle\Entity;

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
 * @AssertCityAssociatedToPostalCode(postalCodeField="postalCode", cityField="city", message="donation.city_not_associated_to_postal_code")
 */
class Donation
{
    /**
     * @var UuidInterface
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
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\Choice(choices={"male", "female"}, message="donation.gender.invalid_choice", strict=true)
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     *
     * @Assert\NotBlank(message="donation.firstName.not_blank")
     * @Assert\Length(max=50, maxMessage="donation.firstName.length_max")
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     *
     * @Assert\NotBlank(message="donation.lastName.not_blank")
     * @Assert\Length(max=50, maxMessage="donation.lastName.length_max")
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\NotBlank(message="donation.email.not_blank")
     * @Assert\Email(message="donation.email.invalid")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\NotBlank(message="donation.country.not_blank")
     * @AssertUnitedNationsCountry(message="donation.country.invalid")
     */
    private $country;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=11, nullable=true)
     *
     * @AssertFrenchPostalCode(message="donation.postalCode.invalid")
     */
    private $postalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     *
     * @AssertFrenchCity(message="donation.city.invalid")
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     *
     * @Assert\Length(max=150, maxMessage="donation.address.length_max")
     */
    private $address;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $payboxResultCode;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
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
     * @ORM\Column(type="string", length=50, nullable=true)
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

    public static function creatUuid(): UuidInterface
    {
        return Uuid::uuid4();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): Donation
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount): Donation
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
     * @return Donation
     */
    public function setGender($gender): Donation
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
     * @return Donation
     */
    public function setLastName($lastName): Donation
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
     * @return Donation
     */
    public function setFirstName($firstName): Donation
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
     * @return Donation
     */
    public function setEmail($email): Donation
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
     * @return Donation
     */
    public function setAddress($address): Donation
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
     * @return Donation
     */
    public function setPostalCode($postalCode): Donation
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
     * @return Donation
     */
    public function setCity($city): Donation
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
     * @return Donation
     */
    public function setCountry($country): Donation
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
     * @return Donation
     */
    public function setPhone($phone): Donation
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

    public function setPayboxPayload(array $payboxPayload): Donation
    {
        $this->payboxPayload = $payboxPayload;

        return $this;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): Donation
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

    public function setClientIp(string $clientIp): Donation
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

    public function setDonatedAt(\DateTime $donatedAt): Donation
    {
        $this->donatedAt = $donatedAt;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}

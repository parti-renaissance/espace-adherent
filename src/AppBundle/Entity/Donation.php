<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;

/**
 * @ORM\Table(name="donations")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DonationRepository")
 */
class Donation
{
    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\GreaterThan(value=0, message="donation.amount.greater_than_0")
     * @Assert\LessThanOrEqual(value=7500, message="donation.amount.less_than_7500")
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\Choice(choices={"male", "female"}, message="donation.gender.invalid_choice")
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     *
     * @Assert\Length(max=50, maxMessage="donation.lastName.length_max")
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\Length(max=50, maxMessage="donation.firstName.length_max")
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\Email(message="donation.email.invalid")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10)
     */
    private $country;

    /**
     * @var PhoneNumber
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    private $payboxRcode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    private $payboxAuth;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    private $payboxPaid;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $payboxPayload;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $finished;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $clientIp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $donatedAt;

    public function __construct()
    {
        $this->donatedAt = new \DateTime();
        $this->country = 'FR';
        $this->finished = false;

        $this->phone = new PhoneNumber();
        $this->phone->setCountryCode(33);
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

    public function setAmount(int $amount): Donation
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

    public function setGender(string $gender): Donation
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

    public function setLastName(string $lastName): Donation
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

    public function setFirstName(string $firstName): Donation
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

    public function setEmail(string $email): Donation
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

    public function setPostalCode(string $postalCode): Donation
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

    public function setCity(string $city): Donation
    {
        $this->city = $city;
        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): Donation
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
    public function getPayboxRcode()
    {
        return $this->payboxRcode;
    }

    public function setPayboxRcode(string $payboxRcode): Donation
    {
        $this->payboxRcode = $payboxRcode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPayboxAuth()
    {
        return $this->payboxAuth;
    }

    public function setPayboxAuth(string $payboxAuth): Donation
    {
        $this->payboxAuth = $payboxAuth;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPayboxPaid()
    {
        return $this->payboxPaid;
    }

    public function setPayboxPaid(string $payboxPaid): Donation
    {
        $this->payboxPaid = $payboxPaid;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPayboxPayload()
    {
        return $this->payboxPayload;
    }

    public function setPayboxPayload(string $payboxPayload): Donation
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

    public function getDonatedAt(): \DateTime
    {
        return $this->donatedAt;
    }

    public function setDonatedAt(\DateTime $donatedAt): Donation
    {
        $this->donatedAt = $donatedAt;
        return $this;
    }
}

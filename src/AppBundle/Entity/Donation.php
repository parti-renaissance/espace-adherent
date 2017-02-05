<?php

namespace AppBundle\Entity;

use AppBundle\Exception\InitializedEntityException;
use AppBundle\Geocoder\GeoPointInterface;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="donations")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DonationRepository")
 */
class Donation implements GeoPointInterface
{
    use EntityIdentityTrait;
    use EntityCrudTrait;
    use EntityPostAddressTrait;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @ORM\Column(length=6)
     */
    private $gender;

    /**
     * @ORM\Column(length=50)
     */
    private $firstName;

    /**
     * @ORM\Column(length=50)
     */
    private $lastName;

    /**
     * @ORM\Column
     */
    private $emailAddress;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(length=100, nullable=true)
     */
    private $payboxResultCode;

    /**
     * @ORM\Column(length=100, nullable=true)
     */
    private $payboxAuthorizationCode;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $payboxPayload;

    /**
     * @ORM\Column(type="boolean")
     */
    private $finished;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    private $clientIp;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $donatedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(
        int $amount,
        string $gender,
        string $firstName,
        string $lastName,
        string $emailAddress,
        PostAddress $postAddress,
        PhoneNumber $phone
    ) {
        $this->amount = $amount;
        $this->gender = $gender;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->emailAddress = $emailAddress;
        $this->postAddress = $postAddress;
        $this->phone = $phone;
        $this->finished = false;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->lastName.' '.$this->firstName.' ('.($this->amount / 100).' €)';
    }

    public function init(string $clientIp)
    {
        if (null !== $this->uuid) {
            throw new InitializedEntityException($this);
        }

        $this->uuid = Uuid::uuid4();
        $this->clientIp = $clientIp;
    }

    public function finish(array $payboxPayload)
    {
        $this->finished = true;
        $this->payboxPayload = $payboxPayload;
        $this->payboxResultCode = $payboxPayload['result'];

        if (isset($payboxPayload['authorization'])) {
            $this->payboxAuthorizationCode = $payboxPayload['authorization'];
        }

        if ($this->payboxResultCode === '00000') {
            $this->donatedAt = new \DateTimeImmutable();
        }
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function isSuccessful(): bool
    {
        return $this->finished && $this->donatedAt;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getAmountInEuros()
    {
        return $this->amount / 100;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getPayboxResultCode()
    {
        return $this->payboxResultCode;
    }

    public function getPayboxAuthorizationCode()
    {
        return $this->payboxAuthorizationCode;
    }

    public function getPayboxPayload()
    {
        return $this->payboxPayload;
    }

    public function getPayboxPayloadAsJson()
    {
        return json_encode($this->payboxPayload, JSON_PRETTY_PRINT);
    }

    public function getFinished()
    {
        return $this->finished;
    }

    public function getClientIp()
    {
        return $this->clientIp;
    }

    public function getDonatedAt()
    {
        return $this->donatedAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}

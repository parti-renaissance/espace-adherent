<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Geocoder\GeoPointInterface;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="donations")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DonationRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Donation implements GeoPointInterface
{
    use EntityIdentityTrait;
    use EntityCrudTrait;
    use EntityPostAddressTrait;
    use EntityPersonNameTrait;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $duration = PayboxPaymentSubscription::NONE;

    /**
     * @ORM\Column(length=6)
     */
    private $gender;

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
        UuidInterface $uuid,
        int $amount,
        string $gender,
        string $firstName,
        string $lastName,
        string $emailAddress,
        PostAddress $postAddress,
        ?PhoneNumber $phone,
        string $clientIp,
        int $frequency
    ) {
        $this->uuid = $uuid;
        $this->amount = $amount;
        $this->gender = $gender;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->emailAddress = $emailAddress;
        $this->postAddress = $postAddress;
        $this->phone = $phone;
        $this->clientIp = $clientIp;
        $this->finished = false;
        $this->createdAt = new \DateTime();
        $this->duration = $frequency;
    }

    public function __toString()
    {
        return $this->lastName.' '.$this->firstName.' ('.($this->amount / 100).' €)';
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
            $this->donatedAt = new \DateTime();
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

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function hasSubscription(): bool
    {
        return PayboxPaymentSubscription::NONE !== $this->duration;
    }

    public function hasUnlimitedSubscription(): bool
    {
        return PayboxPaymentSubscription::UNLIMITED === $this->duration;
    }

    public function getAmountInEuros()
    {
        return $this->amount / 100;
    }

    public function getGender()
    {
        return $this->gender;
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

    public function getDonatedAt(): ?\DateTimeInterface
    {
        return $this->donatedAt;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
}

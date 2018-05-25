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
    private $duration;

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
    private $finished = false;

    /**
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

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $subscriptionEndedAt;

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
        int $duration = PayboxPaymentSubscription::NONE
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
        $this->createdAt = new \DateTime();
        $this->duration = $duration;
    }

    public function __toString(): string
    {
        return $this->lastName.' '.$this->firstName.' ('.($this->amount / 100).' €)';
    }

    public function finish(array $payboxPayload): void
    {
        $this->finished = true;
        $this->payboxPayload = $payboxPayload;
        $this->payboxResultCode = $payboxPayload['result'];

        if (isset($payboxPayload['authorization'])) {
            $this->payboxAuthorizationCode = $payboxPayload['authorization'];
        }

        if ('00000' === $this->payboxResultCode) {
            $this->donatedAt = new \DateTime();
        }
    }

    public function stopSubscription(): void
    {
        $this->setSubscriptionEndedAt(new \DateTime());
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function isSuccessful(): bool
    {
        return $this->finished && $this->donatedAt;
    }

    public function getAmount(): int
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
        return (float) $this->amount / 100;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function getPayboxResultCode(): ?string
    {
        return $this->payboxResultCode;
    }

    public function getPayboxAuthorizationCode(): ?string
    {
        return $this->payboxAuthorizationCode;
    }

    public function getPayboxPayload(): ?array
    {
        return $this->payboxPayload;
    }

    public function getPayboxPayloadAsJson(): string
    {
        return json_encode($this->payboxPayload, JSON_PRETTY_PRINT);
    }

    public function getFinished(): bool
    {
        return $this->finished;
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function getDonatedAt(): ?\DateTimeInterface
    {
        return $this->donatedAt;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getRetryPayload(): array
    {
        $payload = [
            'ge' => $this->gender,
            'ln' => $this->lastName,
            'fn' => $this->firstName,
            'em' => urlencode($this->emailAddress),
            'co' => $this->getCountry(),
            'pc' => $this->getPostalCode(),
            'ci' => $this->getCityName(),
            'ad' => urlencode($this->getAddress()),
        ];

        if ($this->phone) {
            $payload['phc'] = $this->phone->getCountryCode();
            $payload['phn'] = $this->phone->getNationalNumber();
        }

        return $payload;
    }

    public function getSubscriptionEndedAt(): ?\DateTime
    {
        return $this->subscriptionEndedAt;
    }

    public function setSubscriptionEndedAt(?\DateTime $subscriptionEndedAt): void
    {
        $this->subscriptionEndedAt = $subscriptionEndedAt;
    }

    public function nextDonationAt(\DateTime $fromDay = null): \DateTime
    {
        if (!$this->hasSubscription()) {
            throw new \LogicException('Donation without subscription can\'t have next donation date.');
        }

        if (!$fromDay) {
            $fromDay = new \DateTime();
        }

        $donationDate = clone $this->donatedAt;

        return $donationDate->modify(
            sprintf('+%d months', $donationDate->diff($fromDay)->m + 1)
        );
    }
}

<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Geocoder\GeoPointInterface;
use Cake\Chronos\Chronos;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="donations", indexes={
 *     @ORM\Index(name="donation_uuid_idx", columns={"uuid"}),
 *     @ORM\Index(name="donation_email_idx", columns={"email_address"}),
 *     @ORM\Index(name="donation_duration_idx", columns={"duration"}),
 *     @ORM\Index(name="donation_status_idx", columns={"status"})
 * })
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DonationRepository")
 *
 * @ORM\EntityListeners({"AppBundle\EntityListener\DonationListener"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class Donation implements GeoPointInterface
{
    use EntityIdentityTrait;
    use EntityCrudTrait;
    use EntityPostAddressTrait;
    use EntityPersonNameTrait;

    public const STATUS_WAITING_CONFIRMATION = 'waiting_confirmation';
    public const STATUS_SUBSCRIPTION_IN_PROGRESS = 'subscription_in_progress';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_ERROR = 'error';

    public const TYPE_CB = 'cb';
    public const TYPE_CHECK = 'check';
    public const TYPE_TRANSFER = 'transfer';

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $type;

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
     * @ORM\Column(nullable=true)
     */
    private $emailAddress;

    /**
     * We keep this property for legacy datas
     *
     * @var PhoneNumber|null
     *
     * @ORM\Column(type="phone_number", nullable=true)
     */
    private $phone;

    /**
     * Client IP is registered only one time when the order(donation) is created
     *
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     */
    private $clientIp;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $subscriptionEndedAt;

    /**
     * @ORM\Column(length=25)
     */
    private $status;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\Column(nullable=true)
     */
    private $payboxOrderRef;

    /**
     * @ORM\Column(nullable=true)
     */
    private $checkNumber;

    /**
     * @ORM\Column(nullable=true)
     */
    private $transferNumber;

    /**
     * @ORM\Column(length=2, nullable=true)
     */
    private $nationality;

    /**
     * @var Donator|null
     *
     * @ORM\ManyToOne(targetEntity="Donator", inversedBy="donations")
     */
    private $donator;

    public function __construct(
        UuidInterface $uuid,
        string $type,
        int $amount,
        string $gender,
        string $firstName,
        string $lastName,
        ?string $emailAddress,
        PostAddress $postAddress,
        ?string $clientIp,
        int $duration = PayboxPaymentSubscription::NONE,
        string $payboxOrderRef = null,
        string $nationality = null,
        Donator $donator = null,
        \DateTimeInterface $createdAt = null
    ) {
        $this->uuid = $uuid;
        $this->type = $type;
        $this->amount = $amount;
        $this->gender = $gender;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->emailAddress = $emailAddress;
        $this->postAddress = $postAddress;
        $this->clientIp = $clientIp;
        $this->createdAt = $createdAt ?? new Chronos();
        $this->duration = $duration;
        $this->payboxOrderRef = $payboxOrderRef;
        $this->status = self::STATUS_WAITING_CONFIRMATION;
        $this->nationality = $nationality;
        $this->donator = $donator;
    }

    public function __toString(): string
    {
        return sprintf('%s %s (%.2f €)', $this->lastName, $this->firstName, $this->amount / 100);
    }

    public function processPayload(array $payboxPayload): Transaction
    {
        $transaction = new Transaction($this, $payboxPayload);

        if ($transaction->isSuccessful()) {
            $this->status = self::STATUS_FINISHED;
            if (PayboxPaymentSubscription::NONE !== $this->duration) {
                $this->status = self::STATUS_SUBSCRIPTION_IN_PROGRESS;
            }
        } else {
            $this->status = self::STATUS_ERROR;
        }

        return $transaction;
    }

    public function stopSubscription(): void
    {
        $this->subscriptionEndedAt = new \DateTimeImmutable();
        $this->status = self::STATUS_CANCELED;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getDuration(): int
    {
        return $this->duration;
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

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function getCreatedAt(): \DateTimeImmutable
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
            'na' => $this->getNationality(),
            'pc' => $this->getPostalCode(),
            'ci' => $this->getCityName(),
            'ad' => urlencode($this->getAddress()),
        ];

        return $payload;
    }

    public function getSubscriptionEndedAt(): ?\DateTimeInterface
    {
        return $this->subscriptionEndedAt;
    }

    public function nextDonationAt(\DateTime $fromDay = null): \DateTimeInterface
    {
        if (!$this->hasSubscription()) {
            throw new \LogicException('Donation without subscription can\'t have next donation date.');
        }

        if (!$fromDay) {
            $fromDay = new \DateTime();
        }

        $donationDate = clone $this->createdAt;

        return $donationDate->modify(
            sprintf('+%d months', $donationDate->diff($fromDay)->m + 1)
        );
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function hasError(): bool
    {
        return self::STATUS_ERROR === $this->getStatus();
    }

    public function isFinished(): bool
    {
        return self::STATUS_FINISHED === $this->getStatus();
    }

    public function isCanceled(): bool
    {
        return self::STATUS_CANCELED === $this->getStatus();
    }

    public function isWaitingConfirmation(): bool
    {
        return self::STATUS_WAITING_CONFIRMATION === $this->getStatus();
    }

    public function isSubscriptionInProgress(): bool
    {
        return self::STATUS_SUBSCRIPTION_IN_PROGRESS === $this->getStatus();
    }

    public function getPayboxOrderRef(): ?string
    {
        return $this->payboxOrderRef;
    }

    public function setPayboxOrderRef(string $payboxOrderRef): void
    {
        $this->payboxOrderRef = $payboxOrderRef;
    }

    public function getCheckNumber(): ?string
    {
        return $this->checkNumber;
    }

    public function setCheckNumber(?string $checkNumber): void
    {
        $this->checkNumber = $checkNumber;
    }

    public function getTransferNumber(): ?string
    {
        return $this->transferNumber;
    }

    public function setTransferNumber(?string $transferNumber): void
    {
        $this->transferNumber = $transferNumber;
    }

    public function getPayboxOrderRefWithSuffix(): string
    {
        return $this->payboxOrderRef.PayboxPaymentSubscription::getCommandSuffix($this->amount, $this->duration);
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(string $nationality): void
    {
        $this->nationality = $nationality;
    }

    public function getDonator(): ?Donator
    {
        return $this->donator;
    }

    public function setDonator(?Donator $donator): void
    {
        $this->donator = $donator;
    }
}

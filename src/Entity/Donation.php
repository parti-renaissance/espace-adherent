<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Geocoder\GeoPointInterface;
use Cake\Chronos\Chronos;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="donations", indexes={
 *     @ORM\Index(name="donation_uuid_idx", columns={"uuid"}),
 *     @ORM\Index(name="donation_duration_idx", columns={"duration"}),
 *     @ORM\Index(name="donation_status_idx", columns={"status"})
 * })
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DonationRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Donation implements GeoPointInterface
{
    use EntityIdentityTrait;
    use EntityCrudTrait;
    use EntityPostAddressTrait;

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
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $filename;

    /**
     * @var UploadedFile|null
     *
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={
     *         "application/pdf",
     *         "application/x-pdf",
     *         "image/*"
     *     }
     * )
     */
    private $file;

    private $removeFile = false;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var Donator|null
     *
     * @ORM\ManyToOne(targetEntity="Donator", inversedBy="donations")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $donator;

    /**
     * @var Transaction[]
     *
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="donation")
     */
    private $transactions;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\DonationTag")
     */
    private $tags;

    public function __construct(
        UuidInterface $uuid = null,
        string $type = null,
        int $amount = null,
        PostAddress $postAddress = null,
        ?string $clientIp = null,
        int $duration = PayboxPaymentSubscription::NONE,
        string $payboxOrderRef = null,
        string $nationality = null,
        Donator $donator = null,
        \DateTimeInterface $createdAt = null
    ) {
        $this->uuid = $uuid;
        $this->type = $type;
        $this->amount = $amount;
        $this->postAddress = $postAddress;
        $this->clientIp = $clientIp;
        $this->createdAt = $createdAt ?? new Chronos();
        $this->duration = $duration;
        $this->payboxOrderRef = $payboxOrderRef;
        $this->status = self::STATUS_WAITING_CONFIRMATION;
        $this->nationality = $nationality;
        $this->donator = $donator;
        $this->tags = new ArrayCollection();
        $this->transactions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf(
            '%.2f € (%s)',
            $this->amount / 100,
            $this->type
        );
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

        $this->addTransaction($transaction);

        return $transaction;
    }

    public function stopSubscription(): void
    {
        $this->subscriptionEndedAt = new \DateTimeImmutable();
        $this->status = self::STATUS_CANCELED;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getDuration(): ?int
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

    public function getAmountInEuros(): float
    {
        return (float) $this->amount / 100;
    }

    public function setAmountInEuros(int $amountInEuros): void
    {
        $this->amount = $amountInEuros * 100;
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
        if (!$donator = $this->donator) {
            throw new \LogicException('Can not build a retry payload for this donation without a donator');
        }

        $payload = [
            'ge' => $donator->getGender(),
            'ln' => $donator->getLastName(),
            'fn' => $donator->getFirstName(),
            'em' => urlencode($donator->getEmailAddress()),
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

    /**
     * @return Transaction[]|Collection
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): void
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
        }
    }

    public function setLastSuccessfulDonation(): void
    {
        $this->donator->setLastSuccessfulDonation($this);
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(DonationTag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    public function removeTag(DonationTag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    public function hasFileUploaded(): bool
    {
        return null !== $this->filename;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function removeFilename(): void
    {
        $this->filename = null;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function getRemoveFile(): bool
    {
        return $this->removeFile;
    }

    public function setRemoveFile(bool $removeFile): void
    {
        $this->removeFile = $removeFile;
    }

    public function getFilePathWithDirectory(): string
    {
        return sprintf('%s/%s', 'files/donations', $this->filename);
    }

    public function setFilenameFromUploadedFile(): void
    {
        $this->filename = sprintf('%s.%s',
            md5(sprintf('%s@%s', $this->getUuid(), $this->file->getClientOriginalName())),
            $this->file->getClientOriginalExtension()
        );
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }
}

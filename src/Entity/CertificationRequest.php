<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CertificationRequestRepository")
 * @ORM\Table(name="certification_request")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CertificationRequest
{
    use EntityIdentityTrait;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REFUSED = 'refused';
    public const STATUS_BLOCKED = 'blocked';

    public const STATUS_CHOICES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REFUSED,
        self::STATUS_BLOCKED,
    ];

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var string|null
     *
     * @ORM\Column(length=20)
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column
     */
    private $documentName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=30)
     */
    private $documentMimeType;

    /**
     * @var UploadedFile|null
     *
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={
     *         "application/pdf",
     *         "application/x-pdf",
     *         "image/jpeg",
     *         "image/png"
     *     },
     *     mimeTypesMessage="certification_request.document.mime_type"
     * )
     */
    private $document;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity=Adherent::class, inversedBy="certificationRequests")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $adherent;

    /**
     * @var Administrator|null
     *
     * @ORM\ManyToOne(targetEntity=Administrator::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $processedBy;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $processedAt;

    public function __construct(Adherent $adherent)
    {
        $this->createdAt = new \DateTime();
        $this->adherent = $adherent;
        $this->uuid = Uuid::uuid4();
        $this->status = self::STATUS_PENDING;
    }

    public function __toString()
    {
        return (string) $this->adherent;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDocumentName(): ?string
    {
        return $this->documentName;
    }

    public function getDocumentMimeType(): ?string
    {
        return $this->documentMimeType;
    }

    public function getDocumentExtension(): ?string
    {
        if (!$extension = strrchr($this->documentName, '.')) {
            return null;
        }

        return substr($extension, 1);
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getProcessedBy(): ?Administrator
    {
        return $this->processedBy;
    }

    public function getProcessedAt(): ?\DateTime
    {
        return $this->processedAt;
    }

    public function process(Administrator $administrator): void
    {
        $this->processedBy = $administrator;
        $this->processedAt = new \DateTime();
    }

    public function isProcessed(): bool
    {
        return $this->processedBy || $this->processedAt;
    }

    public function isPending(): bool
    {
        return self::STATUS_PENDING === $this->status;
    }

    public function isApproved(): bool
    {
        return self::STATUS_APPROVED === $this->status;
    }

    public function isRefused(): bool
    {
        return self::STATUS_REFUSED === $this->status;
    }

    public function isBlocked(): bool
    {
        return self::STATUS_BLOCKED === $this->status;
    }

    public function approve(): void
    {
        $this->status = self::STATUS_APPROVED;
    }

    public function refuse(): void
    {
        $this->status = self::STATUS_REFUSED;
    }

    public function block(): void
    {
        $this->status = self::STATUS_BLOCKED;
    }

    public function getDocument(): ?UploadedFile
    {
        return $this->document;
    }

    public function setDocument(?UploadedFile $document): void
    {
        $this->document = $document;
    }

    public function getPathWithDirectory(): string
    {
        return sprintf('%s/%s', 'files/certification_requests/document', $this->documentName);
    }

    public function processDocument(UploadedFile $document): void
    {
        $this->documentName = sprintf('%s.%s',
            $this->getUuid(),
            $document->getClientOriginalExtension()
        );

        $this->documentMimeType = $document->getMimeType();
    }
}

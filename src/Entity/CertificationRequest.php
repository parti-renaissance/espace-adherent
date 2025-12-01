<?php

declare(strict_types=1);

namespace App\Entity;

use App\Adherent\Certification\CertificationRequestRefuseCommand;
use App\Repository\CertificationRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CertificationRequestRepository::class)]
class CertificationRequest implements \Stringable
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

    public const OCR_STATUS_PRE_REFUSED = 'pre_refused';
    public const OCR_STATUS_PRE_APPROVED = 'pre_approved';

    public const OCR_STATUS_CHOICES = [
        self::OCR_STATUS_PRE_REFUSED,
        self::OCR_STATUS_PRE_APPROVED,
    ];

    public const MIME_TYPE_JPG = 'image/jpeg';
    public const MIME_TYPE_PNG = 'image/png';
    public const MIME_TYPE_PDF = 'application/pdf';

    public const MIME_TYPES = [
        self::MIME_TYPE_JPG,
        self::MIME_TYPE_PNG,
    ];

    /**
     * @var \DateTime
     */
    #[Groups(['certification_request_read'])]
    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    /**
     * @var string|null
     */
    #[Groups(['certification_request_read'])]
    #[ORM\Column(length: 20)]
    private $status = self::STATUS_PENDING;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $documentName;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 30, nullable: true)]
    private $documentMimeType;

    /**
     * @var UploadedFile|null
     */
    #[Assert\File(maxSize: '5M', mimeTypes: CertificationRequest::MIME_TYPES, mimeTypesMessage: 'certification_request.document.mime_type')]
    #[Assert\NotBlank]
    private $document;

    /**
     * @var Adherent
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'certificationRequests')]
    private $adherent;

    /**
     * @var Administrator|null
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private $processedBy;

    /**
     * @var \DateTime|null
     */
    #[Groups(['certification_request_read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $processedAt;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 30, nullable: true)]
    private $blockReason;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $customBlockReason;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $blockComment;

    /**
     * @var string|null
     */
    #[Groups(['certification_request_read'])]
    #[ORM\Column(length: 30, nullable: true)]
    private $refusalReason;

    /**
     * @var string|null
     */
    #[Groups(['certification_request_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $customRefusalReason;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $refusalComment;

    /**
     * @var array|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private $ocrPayload;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $ocrStatus;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $ocrResult;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $foundDuplicatedAdherent;

    public function __construct(Adherent $adherent)
    {
        $this->createdAt = new \DateTime();
        $this->adherent = $adherent;
        $this->uuid = Uuid::uuid4();
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

    public function isPdfDocument(): bool
    {
        return self::MIME_TYPE_PDF === $this->documentMimeType;
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

    public function process(?Administrator $administrator = null): void
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

    public function refuse(?string $reason, ?string $customReason = null, ?string $comment = null): void
    {
        $this->status = self::STATUS_REFUSED;
        $this->refusalReason = $reason;
        $this->customRefusalReason = $customReason;
        $this->refusalComment = $comment;
    }

    public function block(?string $reason, ?string $customReason = null, ?string $comment = null): void
    {
        $this->status = self::STATUS_BLOCKED;
        $this->blockReason = $reason;
        $this->customBlockReason = $customReason;
        $this->blockComment = $comment;
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
        return \sprintf('%s/%s', 'files/certification_requests/document', $this->documentName);
    }

    public function processDocument(UploadedFile $document): void
    {
        $this->documentName = \sprintf('%s.%s',
            $this->getUuid(),
            $document->getClientOriginalExtension()
        );

        $this->documentMimeType = $document->getMimeType();
    }

    public function removeDocument(): void
    {
        $this->documentName = null;
        $this->documentMimeType = null;
    }

    public function hasDocument(): bool
    {
        return null !== $this->documentName;
    }

    public function getBlockReason(): ?string
    {
        return $this->blockReason;
    }

    public function setBlockReason(?string $blockReason): void
    {
        $this->blockReason = $blockReason;
    }

    public function getCustomBlockReason(): ?string
    {
        return $this->customBlockReason;
    }

    public function setCustomBlockReason(?string $customBlockReason): void
    {
        $this->customBlockReason = $customBlockReason;
    }

    public function getBlockComment(): ?string
    {
        return $this->blockComment;
    }

    public function setBlockComment(?string $blockComment): void
    {
        $this->blockComment = $blockComment;
    }

    public function getRefusalReason(): ?string
    {
        return $this->refusalReason;
    }

    public function setRefusalReason(?string $refusalReason): void
    {
        $this->refusalReason = $refusalReason;
    }

    public function getCustomRefusalReason(): ?string
    {
        return $this->customRefusalReason;
    }

    public function setCustomRefusalReason(?string $customRefusalReason): void
    {
        $this->customRefusalReason = $customRefusalReason;
    }

    public function getRefusalComment(): ?string
    {
        return $this->refusalComment;
    }

    public function setRefusalComment(?string $refusalComment): void
    {
        $this->refusalComment = $refusalComment;
    }

    public function isRefusedWithOtherReason(): bool
    {
        return CertificationRequestRefuseCommand::REFUSAL_REASON_OTHER === $this->refusalReason;
    }

    public function getFoundDuplicatedAdherent(): ?Adherent
    {
        return $this->foundDuplicatedAdherent;
    }

    public function setFoundDuplicatedAdherent(?Adherent $foundDuplicatedAdherent): void
    {
        $this->foundDuplicatedAdherent = $foundDuplicatedAdherent;
    }

    public function getRefusalReasonKey(): string
    {
        return 'certification_request.refusal_reason.'.$this->refusalReason;
    }

    public function getOcrPayload(): ?array
    {
        return $this->ocrPayload;
    }

    public function setOcrPayload(?array $ocrPayload): void
    {
        $this->ocrPayload = $ocrPayload;
    }

    public function getOcrStatus(): ?string
    {
        return $this->ocrStatus;
    }

    public function setOcrStatus(?string $ocrStatus): void
    {
        $this->ocrStatus = $ocrStatus;
    }

    public function getOcrResult(): ?string
    {
        return $this->ocrResult;
    }

    public function setOcrResult(?string $ocrResult): void
    {
        $this->ocrResult = $ocrResult;
    }

    public function isPreApproved(): bool
    {
        return self::OCR_STATUS_PRE_APPROVED === $this->ocrStatus;
    }

    public function isPreRefused(): bool
    {
        return self::OCR_STATUS_PRE_REFUSED === $this->ocrStatus;
    }

    public function resetOcr(): void
    {
        $this->ocrStatus = null;
        $this->ocrResult = null;
        $this->ocrPayload = null;
    }

    public function cleanOcr(): void
    {
        if (isset($this->ocrPayload['text'])) {
            $this->ocrPayload['text'] = null;
        }
    }
}

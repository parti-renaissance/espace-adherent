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
    use EntityTimestampableTrait;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REFUSED = 'refused';

    /**
     * @var string|null
     *
     * @ORM\Column(length=20)
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $documentName;

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
     * @var array|null
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $annotations;

    /**
     * @var Adherent
     *
     * @ORM\OneToOne(targetEntity=Adherent::class, mappedBy="certificationRequest")
     */
    private $adherent;

    public function __construct(Adherent $adherent)
    {
        $this->adherent = $adherent;
        $this->uuid = Uuid::uuid4();
        $this->status = self::STATUS_PENDING;
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->adherent, $this->status);
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getAnnotations(): ?array
    {
        return $this->annotations;
    }

    public function setAnnotations(?array $annotations): void
    {
        $this->annotations = $annotations;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
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

    public function approve(): void
    {
        $this->status = self::STATUS_APPROVED;
    }

    public function refuse(): void
    {
        $this->status = self::STATUS_REFUSED;
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

    public function setDocumentNameFromUploadedFile(UploadedFile $document): void
    {
        $this->documentName = sprintf('%s.%s',
            md5(sprintf('%s@%s', $this->getUuid(), $document->getClientOriginalName())),
            $document->getClientOriginalExtension()
        );
    }
}

<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="user_documents",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="document_uuid_unique", columns="uuid")
 *     }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserDocumentRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class UserDocument
{
    use EntityIdentityTrait;

    public const TYPE_COMMITTEE_CONTACT = 'committee_contact';
    public const TYPE_COMMITTEE_FEED = 'committee_feed';
    public const TYPE_EVENT = 'event';
    public const TYPE_REFERENT = 'referent';
    public const TYPE_IDEA_ANSWER = 'idea_answer';
    public const TYPE_ADHERENT_MESSAGE = 'adherent_message';

    public const ALL_TYPES = [
        self::TYPE_COMMITTEE_CONTACT,
        self::TYPE_COMMITTEE_FEED,
        self::TYPE_EVENT,
        self::TYPE_REFERENT,
        self::TYPE_IDEA_ANSWER,
        self::TYPE_ADHERENT_MESSAGE,
    ];

    /**
     * @var string|null
     *
     * @ORM\Column(length=200)
     *
     * @Assert\Length(max=200, maxMessage="document.validation.filename_length")
     */
    private $originalName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=10)
     *
     * @Assert\Length(max=10)
     */
    private $extension;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\LessThan(
     *     value=5242880,
     *     message="document.validation.max_filesize"
     * )
     */
    private $size;

    /**
     * @var string|null
     *
     * @ORM\Column
     */
    private $mimeType;

    /**
     * @var string
     *
     * @ORM\Column(length=20)
     *
     * @Assert\Choice(callback="allTypes")
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    private function __construct(string $type, string $name, string $extension, int $size, string $createdAt = 'now')
    {
        $this->uuid = Uuid::uuid4();
        $this->originalName = $name;
        $this->extension = $extension;
        $this->size = $size;
        $this->mimeType = $type;
        $this->createdAt = new \DateTimeImmutable($createdAt);
    }

    public function __toString()
    {
        return $this->originalName ?: '';
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): void
    {
        $this->originalName = $originalName;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        if ($this->createdAt instanceof \DateTime) {
            $this->createdAt = \DateTimeImmutable::createFromMutable($this->createdAt);
        }

        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPath(): string
    {
        return sprintf('user_documents/%ss/%s.%s', $this->type, $this->getUuid()->toString(), $this->getExtension());
    }

    public static function allTypes(): array
    {
        return self::ALL_TYPES;
    }

    public static function createFromUploadedFile(UploadedFile $uploadedFile): self
    {
        return new self(
            $uploadedFile->getClientMimeType(),
            $uploadedFile->getClientOriginalName(),
            $uploadedFile->getClientOriginalExtension(),
            $uploadedFile->getClientSize()
        );
    }

    public static function create(
        UuidInterface $uuid,
        string $type,
        string $mimeType,
        string $name,
        string $extension,
        int $size
    ): self {
        $document = new self(
            $mimeType,
            $name,
            $extension,
            $size
        );

        $document->uuid = $uuid;
        $document->type = $type;

        return $document;
    }
}

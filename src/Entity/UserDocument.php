<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\UserDocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: SearchFilter::class, properties: ['type' => 'exact', 'originalName' => 'partial'])]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/v3/upload'),
    ],
    normalizationContext: ['groups' => ['user_document:read']],
    order: ['createdAt' => 'DESC'],
    paginationItemsPerPage: 30,
)]
#[ORM\Entity(repositoryClass: UserDocumentRepository::class)]
#[ORM\Index(fields: ['instanceKey'])]
#[ORM\Table(name: 'user_documents')]
class UserDocument implements \Stringable, InstanceOwnerInterface
{
    use EntityIdentityTrait;
    use AuthorInstanceTrait;
    use EntityTimestampableTrait;

    public const TYPE_COMMITTEE_CONTACT = 'committee_contact';
    public const TYPE_COMMITTEE_FEED = 'committee_feed';
    public const TYPE_EVENT = 'event';
    public const TYPE_REFERENT = 'referent';
    public const TYPE_ADHERENT_MESSAGE = 'adherent_message';
    public const TYPE_PUBLICATION = 'publication';
    public const TYPE_NEWS = 'news';

    public const ALL_TYPES = [
        self::TYPE_COMMITTEE_CONTACT,
        self::TYPE_COMMITTEE_FEED,
        self::TYPE_EVENT,
        self::TYPE_REFERENT,
        self::TYPE_ADHERENT_MESSAGE,
        self::TYPE_NEWS,
        self::TYPE_PUBLICATION,
    ];

    /**
     * @var string|null
     */
    #[Assert\Length(max: 200, maxMessage: 'document.validation.filename_length')]
    #[Groups(['user_document:read'])]
    #[ORM\Column(length: 200)]
    private $originalName;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 10)]
    #[ORM\Column(length: 10)]
    private $extension;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    private $size;

    /**
     * @var string|null
     */
    #[ORM\Column]
    private $mimeType;

    /**
     * @var string
     */
    #[Assert\Choice(callback: 'allTypes')]
    #[ORM\Column(length: 25)]
    private $type;

    private function __construct(string $type, string $name, string $extension, int $size)
    {
        $this->uuid = Uuid::uuid4();
        $this->originalName = $name;
        $this->extension = $extension;
        $this->size = $size;
        $this->mimeType = $type;
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

    public function getFilename(): string
    {
        return pathinfo($this->originalName, \PATHINFO_FILENAME);
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    #[Groups(['user_document:read'])]
    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    #[Groups(['user_document:read'])]
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
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
        return \sprintf('user_documents/%ss/%s.%s', $this->type, $this->getUuid()->toString(), $this->getExtension());
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
            $uploadedFile->getSize()
        );
    }

    public static function create(
        UuidInterface $uuid,
        string $type,
        string $mimeType,
        string $name,
        string $extension,
        int $size,
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

<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get": {
 *             "path": "/jecoute/news",
 *             "method": "GET",
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "uuid",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "Filter News by exact uuid.",
 *                         "example": "a046adbe-9c7b-56a9-a676-6151a6785dda",
 *                     },
 *                     {
 *                         "name": "title",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "Filter News by partial title.",
 *                         "example": "Rassem",
 *                     },
 *                 }
 *             }
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "method": "GET",
 *             "path": "/jecoute/news/{id}",
 *             "swagger_context": {
 *                 "summary": "Retrieves a News resource by UUID.",
 *                 "description": "Retrieves a News resource by UUID.",
 *                 "parameters": {
 *                     {
 *                         "name": "uuid",
 *                         "in": "path",
 *                         "type": "string",
 *                         "description": "The UUID of the News resource.",
 *                         "example": "28",
 *                     }
 *                 }
 *             }
 *         }
 *     },
 *     attributes={
 *         "normalization_context": {"groups": {"jecoute_news_read"}},
 *         "access_control": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')",
 *         "order": {"createdAt": "DESC"},
 *     },
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "uuid": "exact",
 *     "title": "partial",
 * })
 *
 * @ORM\Table(name="jecoute_news")
 * @ORM\Entity
 */
class News
{
    use EntityTimestampableTrait;

    /**
     * @var int|null
     *
     * @ApiProperty(identifier=false)
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var UuidInterface
     *
     * @ApiProperty(identifier=true)
     *
     * @ORM\Column(type="uuid")
     *
     * @SymfonySerializer\Groups({"jecoute_news_read"})
     */
    protected $uuid;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\Length(max=120)
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"jecoute_news_read"})
     */
    protected $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"jecoute_news_read"})
     */
    protected $text;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     *
     * @SymfonySerializer\Groups({"jecoute_news_read"})
     */
    protected $externalLink;

    public function __construct(
        UuidInterface $uuid = null,
        string $title = null,
        string $text = null,
        string $externalLink = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->title = $title;
        $this->text = $text;
        $this->externalLink = $externalLink;
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(?string $externalLink): void
    {
        $this->externalLink = $externalLink;
    }
}

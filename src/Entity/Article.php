<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "order": {"createdAt": "DESC"},
 *         "normalization_context": {
 *             "iri": true,
 *             "groups": {"article_read"}
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/articles",
 *             "normalization_context": {
 *                 "groups": {"article_list_read"},
 *             },
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/articles/{slug}",
 *             "requirements": {"slug": ".+"},
 *         },
 *     },
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"title": "partial", "category.slug": "exact"})
 *
 * @ORM\Table(name="articles")
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 * @ORM\EntityListeners({"App\EntityListener\ArticleListener"})
 *
 * @UniqueEntity(fields={"slug"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Article implements EntityMediaInterface, EntityContentInterface, EntitySoftDeletedInterface, IndexableEntityInterface, EntitySourceableInterface
{
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;
    use EntityContentTrait;
    use UnlayerJsonContentTrait;
    use EntityMediaTrait;
    use EntityPublishableTrait;
    use EntitySourceableTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue
     *
     * @ApiProperty(identifier=false)
     */
    private $id;

    /**
     * @var ArticleCategory|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ArticleCategory")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Assert\NotBlank
     *
     * @Groups({"article_list_read", "article_read"})
     */
    private $category;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     *
     * @Groups({"article_list_read", "article_read"})
     */
    private $publishedAt;

    /**
     * @var ProposalTheme[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="ProposalTheme")
     */
    private $themes;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Media", cascade={"persist"})
     *
     * @Assert\NotBlank
     * @Assert\Valid
     *
     * @Groups({"article_list_read", "article_read"})
     */
    private $media;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"article_list_read", "article_read"})
     */
    private $slug;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
        $this->themes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return ArticleCategory|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(ArticleCategory $category = null): self
    {
        $this->category = $category;

        return $this;
    }

    public function getPublishedAt(): \DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function addTheme(ProposalTheme $theme)
    {
        $this->themes[] = $theme;
    }

    public function removeTheme(ProposalTheme $theme)
    {
        $this->themes->removeElement($theme);
    }

    /**
     * @return ProposalTheme[]|Collection
     */
    public function getThemes()
    {
        return $this->themes;
    }

    public function isIndexable(): bool
    {
        return $this->isPublished() && $this->isNotDeleted() && !$this->isForRenaissance();
    }

    public function getIndexOptions(): array
    {
        return [];
    }
}

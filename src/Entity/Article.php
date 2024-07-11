<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\EntityListener\ArticleListener;
use App\Repository\ArticleRepository;
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
 */
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\EntityListeners([ArticleListener::class])]
#[ORM\Table(name: 'articles')]
#[UniqueEntity(fields: ['slug'])]
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
     * @ApiProperty(identifier=false)
     */
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var ArticleCategory|null
     */
    #[Assert\NotBlank]
    #[Groups(['article_list_read', 'article_read'])]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ArticleCategory::class)]
    private $category;

    /**
     * @var \DateTime
     */
    #[Assert\NotBlank]
    #[Groups(['article_list_read', 'article_read'])]
    #[ORM\Column(type: 'datetime')]
    private $publishedAt;

    /**
     * @var ProposalTheme[]|Collection
     */
    #[ORM\ManyToMany(targetEntity: ProposalTheme::class)]
    private $themes;

    /**
     * @var Media
     */
    #[Assert\NotBlank]
    #[Assert\Valid]
    #[Groups(['article_list_read', 'article_read'])]
    #[ORM\ManyToOne(targetEntity: Media::class, cascade: ['persist'])]
    private $media;

    /**
     * @var string|null
     *
     * @ApiProperty(identifier=true)
     */
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[Groups(['article_list_read', 'article_read'])]
    #[ORM\Column(length: 100, unique: true)]
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

    public function setCategory(?ArticleCategory $category = null): self
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

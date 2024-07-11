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

#[ORM\Table(name: 'articles')]
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\EntityListeners([ArticleListener::class])]
#[UniqueEntity(fields: ['slug'])]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ApiResource(attributes: ['order' => ['createdAt' => 'DESC'], 'normalization_context' => ['iri' => true, 'groups' => ['article_read']]], collectionOperations: ['get' => ['path' => '/articles', 'normalization_context' => ['groups' => ['article_list_read']]]], itemOperations: ['get' => ['path' => '/articles/{slug}', 'requirements' => ['slug' => '.+']]])]
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial', 'category.slug' => 'exact'])]
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
     */
    #[ORM\Column(type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ApiProperty(identifier: false)]
    private $id;

    /**
     * @var ArticleCategory|null
     */
    #[Groups(['article_list_read', 'article_read'])]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ArticleCategory::class)]
    #[Assert\NotBlank]
    private $category;

    /**
     * @var \DateTime
     */
    #[Groups(['article_list_read', 'article_read'])]
    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    private $publishedAt;

    /**
     * @var ProposalTheme[]|Collection
     */
    #[ORM\ManyToMany(targetEntity: ProposalTheme::class)]
    private $themes;

    /**
     * @var Media
     */
    #[Groups(['article_list_read', 'article_read'])]
    #[ORM\ManyToOne(targetEntity: Media::class, cascade: ['persist'])]
    #[Assert\NotBlank]
    #[Assert\Valid]
    private $media;

    /**
     * @var string|null
     */
    #[Groups(['article_list_read', 'article_read'])]
    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ApiProperty(identifier: true)]
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

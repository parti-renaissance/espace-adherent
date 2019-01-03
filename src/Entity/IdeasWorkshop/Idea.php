<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AuthorInterface;
use AppBundle\Entity\Committee;
use AppBundle\Entity\EntityNameSlugTrait;
use AppBundle\Entity\EntityTimestampableTrait;
use AppBundle\Entity\Report\ReportableInterface;
use AppBundle\Entity\VisibleStatusesInterface;
use Cake\Chronos\Chronos;
use AppBundle\Report\ReportType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get": {
 *             "method": "GET",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "status",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "The status of the Idea resource.",
 *                         "enum": IdeaStatusEnum::ALL_STATUSES,
 *                         "example": IdeaStatusEnum::PENDING,
 *                     },
 *                     {
 *                         "name": "name",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "The name of the Idea resource.",
 *                         "example": "écologie",
 *                     },
 *                     {
 *                         "name": "theme.name",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "The theme name of the Idea resource.",
 *                         "example": "armée",
 *                     },
 *                     {
 *                         "name": "author_category",
 *                         "in": "query",
 *                         "type": "uuid",
 *                         "description": "The author category of the Idea resource.",
 *                         "enum": AuthorCategoryEnum::ALL_CATEGORIES,
 *                         "example": AuthorCategoryEnum::ADHERENT,
 *                     },
 *                     {
 *                         "name": "author.uuid",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "The author uuid of the Idea resource.",
 *                         "example": "a046adbe-9c7b-56a9-a676-6151a6785dda",
 *                     },
 *                 }
 *             }
 *         },
 *         "get_my_contributions": {
 *             "method": "GET",
 *             "path": "/ideas/my-contributions",
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *             "normalization_context": {"groups": {"idea_list_read"}}
 *         },
 *         "post": {
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "method": "GET",
 *             "normalization_context": {"groups": {"idea_read"}},
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Idea resource.",
 *                         "example": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
 *                     }
 *                 }
 *             }
 *         },
 *         "put": {"access_control": "object.getAuthor() == user"},
 *         "publish": {
 *             "method": "PUT",
 *             "denormalization_context": {"api_allow_update": false},
 *             "access_control": "object.getAuthor() == user",
 *             "path": "/ideas/{id}/publish",
 *             "controller": "AppBundle\Controller\Api\IdeaPublishController",
 *             "normalization_context": {"groups": {"idea_list_read"}},
 *             "validation_groups": {"idea_publish"}
 *         },
 *         "delete": {"access_control": "object.getAuthor() == user"}
 *     },
 *     attributes={
 *         "normalization_context": {"groups": {"idea_list_read"}},
 *         "denormalization_context": {
 *             "groups": {"idea_write"}
 *         },

 *         "order": {"createdAt": "ASC"}
 *     }
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "status": "exact",
 *     "name": "partial",
 *     "theme.name": "exact",
 *     "author_category": "exact",
 *     "author.uuid": "exact"
 * })
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\IdeaRepository")
 *
 * @ORM\Table(
 *     name="ideas_workshop_idea",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idea_uuid_unique", columns="uuid"),
 *         @ORM\UniqueConstraint(name="idea_slug_unique", columns="slug")
 *     },
 *     indexes={
 *         @ORM\Index(name="idea_workshop_status_idx", columns={"status"}),
 *         @ORM\Index(name="idea_workshop_author_category_idx", columns={"author_category"})
 *     }
 * )
 *
 * @UniqueEntity("slug")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Idea implements AuthorInterface, ReportableInterface, VisibleStatusesInterface
{
    use EntityTimestampableTrait;
    use EntityNameSlugTrait;

    private const PUBLISHED_INTERVAL = 'P3W';

    /**
     * @ApiProperty(identifier=false)
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ApiProperty(identifier=true)
     *
     * @ORM\Column(type="uuid")
     *
     * @SymfonySerializer\Groups({"idea_list_read", "my_committees", "thread_comment_read"})
     */
    protected $uuid;

    /**
     * @ORM\Column
     *
     * @Assert\Length(max=120)
     * @Assert\NotBlank(message="idea.name.not_blank")
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "vote_read"})
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="Theme")
     * @ORM\JoinTable(name="ideas_workshop_ideas_themes")
     *
     * @Assert\Count(min=1, minMessage="idea.theme.min_count", groups={"idea_publish"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "idea_read"})
     */
    private $themes;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     *
     * @Assert\NotBlank(message="idea.category.not_blank", groups={"idea_publish"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "idea_read"})
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity="Need")
     * @ORM\JoinTable(name="ideas_workshop_ideas_needs")
     *
     * @Assert\Count(min=1, minMessage="idea.needs.min_count", groups={"idea_publish"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "idea_read"})
     */
    private $needs;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent", inversedBy="ideas")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\NotNull(message="idea.author.not_null", groups={"idea_publish"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "idea_read"})
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\NotNull(message="idea.published_at.not_null", groups={"idea_publish"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write", "idea_read"})
     */
    private $publishedAt;

    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee")
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     */
    private $committee;

    /**
     * @ORM\Column(length=11, options={"default": IdeaStatusEnum::DRAFT})
     *
     * @Assert\Choice(choices=IdeaStatusEnum::ALL_STATUSES, strict=true)
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context": {
     *             "type": "string",
     *             "enum": IdeaStatusEnum::ALL_STATUSES,
     *             "example": IdeaStatusEnum::DRAFT
     *         }
     *     }
     * )
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="idea", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Count(min=1, minMessage="idea.answers.min_count", groups={"idea_publish"})
     * @Assert\Valid
     *
     * @SymfonySerializer\Groups({"idea_write", "idea_read"})
     */
    private $answers;

    /**
     * @ORM\OneToMany(targetEntity="Vote", mappedBy="idea", cascade={"remove"}, orphanRemoval=true)
     *
     * @ApiSubresource
     */
    private $votes;

    /**
     * @ORM\Column(type="integer", options={"unsigned": true})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_read"})
     */
    private $votesCount = 0;

    /**
     * @ORM\Column(length=9)
     *
     * @Assert\Choice(choices=AuthorCategoryEnum::ALL_CATEGORIES, strict=true)
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context": {
     *             "type": "string",
     *             "enum": AuthorCategoryEnum::ALL_CATEGORIES,
     *             "example": AuthorCategoryEnum::ADHERENT
     *         }
     *     }
     * )
     */
    private $authorCategory;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank(message="idea.description.not_blank", groups={"idea_publish"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "idea_write"})
     */
    private $description;

    public function __construct(
        string $name,
        string $description = null,
        string $authorCategory = AuthorCategoryEnum::ADHERENT,
        \DateTime $publishedAt = null,
        string $status = IdeaStatusEnum::DRAFT,
        Adherent $author = null,
        UuidInterface $uuid = null,
        \DateTime $createdAt = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->author = $author;
        $this->setName($name);
        $this->description = $description;
        $this->authorCategory = $authorCategory;
        $this->publishedAt = $publishedAt;
        $this->status = $status;
        $this->needs = new ArrayCollection();
        $this->themes = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->createdAt = $createdAt ?: new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public static function getVisibleStatuses(): array
    {
        return IdeaStatusEnum::VISIBLE_STATUSES;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): void
    {
        if (!$this->themes->contains($theme)) {
            $this->themes->add($theme);
        }
    }

    public function removeTheme(Theme $theme): void
    {
        $this->themes->removeElement($theme);
    }

    public function getNeeds(): Collection
    {
        return $this->needs;
    }

    public function addNeed(Need $need): void
    {
        if (!$this->needs->contains($need)) {
            $this->needs->add($need);
        }
    }

    public function removeNeed(Need $need): void
    {
        $this->needs->removeElement($need);
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTime $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(?Committee $committee): void
    {
        $this->committee = $committee;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function addAnswer(Answer $answer): void
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setIdea($this);
        }
    }

    public function removeAnswer(Answer $answer): void
    {
        $this->answers->removeElement($answer);
    }

    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addVote(Vote $vote): void
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setIdea($this);
        }
    }

    public function removeVote(Vote $vote): void
    {
        $this->votes->removeElement($vote);
        $this->decrementVotesCount();
    }

    public function getVotes(): Collection
    {
        return $this->votes;
    }

    /**
     * @SymfonySerializer\Groups("idea_list_read")
     */
    public function getDaysBeforeDeadline(): int
    {
        if (!$this->publishedAt) {
            return 0;
        }

        $deadline = $this->publishedAt->add(new \DateInterval(self::PUBLISHED_INTERVAL));
        $now = new Chronos();

        return $deadline <= $now ? 0 : $deadline->diff($now)->d;
    }

    public function isDraft(): bool
    {
        return IdeaStatusEnum::DRAFT === $this->status;
    }

    public function isPending(): bool
    {
        return IdeaStatusEnum::PENDING === $this->status;
    }

    public function isFinalized(): bool
    {
        return IdeaStatusEnum::FINALIZED === $this->status;
    }

    public function isUnpublished(): bool
    {
        return IdeaStatusEnum::UNPUBLISHED === $this->status;
    }

    public function publish(): void
    {
        $this->status = IdeaStatusEnum::PENDING;
        $this->publishedAt = new \DateTime();
    }

    public function getUuidAsString(): string
    {
        return $this->getUuid()->toString();
    }

    public function getAuthorCategory(): string
    {
        return $this->authorCategory;
    }

    public function setAuthorCategory(string $authorCategory): void
    {
        $this->authorCategory = $authorCategory;
    }

    public function getVotesCount(): int
    {
        return $this->votesCount;
    }

    public function incrementVotesCount(int $increment = 1): void
    {
        $this->votesCount += $increment;
    }

    public function decrementVotesCount(int $increment = 1): void
    {
        $this->votesCount -= $increment;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getReportType(): string
    {
        return ReportType::IDEAS_WORKSHOP_IDEA;
    }
}

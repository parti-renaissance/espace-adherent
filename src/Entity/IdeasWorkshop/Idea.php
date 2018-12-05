<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\Entity\EntityNameSlugTrait;
use AppBundle\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get",
 *         "post": {"access_control": "is_granted('IS_AUTHENTICATED_FULLY')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put": {"access_control": "object.getAdherent() == user", "normalization_context": {"groups": {"put"}}},
 *     }
 * )
 *
 * @ORM\Entity
 *
 * @ORM\Table(
 *     name="ideas_workshop_idea",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idea_slug_unique", columns="slug")
 *     }
 * )
 *
 * @UniqueEntity("slug")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Idea implements GroupSequenceProviderInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityNameSlugTrait;

    private const PUBLISHED_INTERVAL = 'P3W';

    /**
     * @ORM\ManyToOne(targetEntity="Theme")
     *
     * @JMS\Groups({"put"})
     *
     * @Assert\NotBlank(groups={"idea_put"})
     */
    private $theme;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     *
     * @JMS\Groups({"put"})
     *
     * @Assert\NotBlank(groups={"idea_put"})
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity="Need")
     * @ORM\JoinTable(name="ideas_workshop_ideas_needs")
     */
    private $needs;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     *
     * @JMS\Groups({"post"})
     *
     * @Assert\NotBlank(groups={"idea_post"})
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\NotBlank(groups={"idea_publish"})
     */
    private $publishedAt;

    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee")
     *
     * @JMS\Groups({"put"})
     *
     * @Assert\NotBlank(groups={"idea_put"})
     */
    private $committee;

    /**
     * @Assert\Choice(
     *     callback={"AppBundle\Entity\IdeasWorkshop\IdeaStatusEnum", "toArray"},
     *     strict=true,
     *     groups={"idea_put"}
     * )
     *
     * @JMS\Groups({"put"})
     *
     * @ORM\Column(length=11, options={"default": IdeaStatusEnum::PENDING})
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="idea")
     *
     * @JMS\Groups({"post"})
     *
     * @Assert\Count(min=1, groups={"idea_post"})
     */
    private $answers;

    public function __construct(
        Adherent $author,
        string $name = null,
        Category $category = null,
        Theme $theme = null,
        Committee $committee = null,
        \DateTime $publishedAt = null,
        string $status = IdeaStatusEnum::PENDING
    ) {
        $this->uuid = Uuid::uuid4();
        $this->setName($name);
        $this->author = $author;
        $this->category = $category;
        $this->theme = $theme;
        $this->committee = $committee;
        $this->publishedAt = $publishedAt;
        $this->status = $status;
        $this->needs = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getTheme(): Theme
    {
        return $this->theme;
    }

    public function setTheme(Theme $theme): void
    {
        $this->theme = $theme;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    public function getNeeds(): ArrayCollection
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

    public function getAnswers(): ArrayCollection
    {
        return $this->answers;
    }

    public function getDaysBeforeDeadline(): int
    {
        $deadline = $this->createdAt->add(new \DateInterval(self::PUBLISHED_INTERVAL));
        $now = new \DateTime();

        return $deadline <= $now ? 0 : $deadline->diff($now)->d;
    }

    public function isPending(): bool
    {
        return IdeaStatusEnum::PENDING === $this->status;
    }

    public function isPublished(): bool
    {
        return IdeaStatusEnum::PUBLISHED === $this->status;
    }

    public function isRefused(): bool
    {
        return IdeaStatusEnum::REFUSED === $this->status;
    }

    public function getGroupSequence()
    {
        if ($this->getId()) {
            return ['idea_post'];
        } else {
            return [['idea_post', 'idea_put']];
        }
    }
}

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
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
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
class Idea
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityNameSlugTrait;

    private const PUBLISHED_INTERVAL = 'P3W';

    /**
     * @ORM\ManyToOne(targetEntity="Theme")
     */
    private $theme;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity="Need")
     * @ORM\JoinTable(name="ideas_workshop_ideas_needs")
     */
    private $needs;

    /**
     * @JMS\Groups({"idea_list"})
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"idea_list"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publishedAt;

    /**
     * @var Committee
     *
     * @JMS\Groups({"idea_list"})
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee")
     */
    private $committee;

    /**
     * @Assert\Choice(
     *     callback={"AppBundle\Entity\IdeasWorkshop\IdeaStatusEnum", "toArray"},
     *     strict=true,
     * )
     *
     * @ORM\Column(length=11, options={"default": IdeaStatusEnum::DRAFT})
     */
    private $status;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $withCommittee;

    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="idea")
     */
    private $answers;

    /**
     * @ORM\OneToMany(targetEntity="Vote", mappedBy="idea")
     */
    private $votes;

    public function __construct(
        UuidInterface $uuid,
        string $name,
        Adherent $author,
        Category $category,
        Theme $theme,
        bool $withCommittee = false,
        Committee $committee = null,
        \DateTime $publishedAt = null,
        string $status = IdeaStatusEnum::DRAFT
    ) {
        $this->uuid = $uuid;
        $this->setName($name);
        $this->author = $author;
        $this->category = $category;
        $this->theme = $theme;
        $this->committee = $committee;
        $this->publishedAt = $publishedAt;
        $this->status = $status;
        $this->withCommittee = $withCommittee;
        $this->needs = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->votes = new ArrayCollection();
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

    public function isWithCommittee(): bool
    {
        return $this->withCommittee;
    }

    public function setWithCommittee(bool $withCommittee): void
    {
        $this->withCommittee = $withCommittee;
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
        }
    }

    public function removeVote(Vote $vote): void
    {
        $this->votes->removeElement($vote);
    }

    public function getVotes(): ArrayCollection
    {
        return $this->votes;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("days_before_deadline"),
     * @JMS\Groups({"idea_list"})
     */
    public function getDaysBeforeDeadline(): int
    {
        $deadline = $this->createdAt->add(new \DateInterval(self::PUBLISHED_INTERVAL));
        $now = new \DateTime();

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

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("uuid"),
     * @JMS\Groups({"idea_list"})
     */
    public function getUuidAsString(): string
    {
        return $this->getUuid()->toString();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("answers"),
     * @JMS\Groups({"idea_list"})
     */
    public function getAnswerSerialized(): Collection
    {
        return $this->answers
            ->filter(function (Answer $answer) {
                return !$answer->getThreads()->isEmpty();
            })
        ;
    }
}

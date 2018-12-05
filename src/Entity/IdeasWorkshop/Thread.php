<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\EntitySoftDeletableTrait;
use AppBundle\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="ideas_workshop_thread")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class Thread
{
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="Answer", inversedBy="threads")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $answer;

    /**
     * @ORM\OneToMany(targetEntity="ThreadComment", mappedBy="thread")
     */
    private $comments;

    /**
     * @Assert\Choice(
     *     callback={"AppBundle\Entity\IdeasWorkshop\ThreadStatusEnum", "toArray"},
     *     strict=true,
     * )
     *
     * @ORM\Column(length=9, options={"default": ThreadStatusEnum::SUBMITTED})
     */
    private $status = ThreadStatusEnum::SUBMITTED;

    public function __construct(string $content, Adherent $author, Answer $answer)
    {
        $this->content = $content;
        $this->author = $author;
        $this->answer = $answer;
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnswer(): Answer
    {
        return $this->answer;
    }

    public function setAnswer(Answer $answer): void
    {
        $this->answer = $answer;
    }

    public function addComment(ThreadComment $comment): void
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setThread($this);
        }
    }

    public function removeComment(ThreadComment $comment): void
    {
        $this->comments->removeElement($comment);
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isSubmitted(): bool
    {
        return ThreadStatusEnum::SUBMITTED === $this->status;
    }

    public function isDeleted(): bool
    {
        return ThreadStatusEnum::DELETED === $this->status;
    }

    public function isApproved(): bool
    {
        return ThreadStatusEnum::APPROVED === $this->status;
    }
}

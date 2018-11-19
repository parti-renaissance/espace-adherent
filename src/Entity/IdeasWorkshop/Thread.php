<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="note_thread")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class Thread
{
    use EntityIdentityTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Answer", inversedBy="threads")
     */
    private $answer;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="thread")
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
    private $status;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getAnswer(): Answer
    {
        return $this->answer;
    }

    public function setAnswer($answer): void
    {
        $this->answer = $answer;
    }

    public function addComment(Comment $comment): void
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }
    }

    public function removeComment(Comment $comment): void
    {
        $this->comments->removeElement($comment);
    }

    public function getComments(): ArrayCollection
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

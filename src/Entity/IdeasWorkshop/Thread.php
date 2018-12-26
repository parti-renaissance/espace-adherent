<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AuthorInterface;
use AppBundle\Entity\Report\ReportableInterface;
use AppBundle\Report\ReportType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"thread_comment_read"}
 *         },
 *         "order": {"createdAt": "ASC"},
 *         "filters": {"thread.answer"}
 *     },
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 * )
 * @ApiFilter(SearchFilter::class, properties={"answer.idea": "exact"})
 *
 * @ORM\Table(name="ideas_workshop_thread")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class Thread extends BaseComment implements AuthorInterface, ReportableInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="Answer", inversedBy="threads")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @SymfonySerializer\Groups("thread_comment_read")
     */
    private $answer;

    /**
     * @ORM\OneToMany(targetEntity="ThreadComment", mappedBy="thread", cascade={"remove"}, orphanRemoval=true)
     * @ApiSubresource
     */
    private $comments;

    public function __construct(
        UuidInterface $uuid,
        string $content,
        Adherent $author,
        Answer $answer,
        string $status = ThreadCommentStatusEnum::POSTED,
        \DateTime $createdAt = null
    ) {
        parent::__construct($uuid, $content, $author, $status);

        $this->answer = $answer;
        $this->createdAt = $createdAt ?: new \DateTime();
        $this->comments = new ArrayCollection();
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

    public function getReportType(): string
    {
        return ReportType::IDEAS_WORKSHOP_THREAD;
    }
}

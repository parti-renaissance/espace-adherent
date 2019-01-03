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
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"thread_comment_read"}
 *         },
 *         "order": {"createdAt": "ASC"},
 *         "filters": {"thread.answer"}
 *     },
 *     collectionOperations={
 *         "get",
 *         "post": {
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Thread resource.",
 *                         "example": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
 *                     }
 *                 }
 *             }
 *         },
 *         "put_status_approve": {
 *             "method": "PUT",
 *             "path": "/threads/{id}/approve",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "denormalization_context": {"api_allow_update": false},
 *             "access_control": "object.getIdeaAuthor() == user",
 *             "controller": "AppBundle\Controller\Api\ThreadController::approveAction",
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Thread resource.",
 *                         "example": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
 *                     }
 *                 }
 *             }
 *         },
 *         "put_status_report": {
 *             "method": "PUT",
 *             "path": "/threads/{id}/report",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "denormalization_context": {"api_allow_update": false},
 *             "access_control": "is_granted('ROLE_ADHERENT') && object.getAuthor() != user",
 *             "controller": "AppBundle\Controller\Api\ThreadController::reportAction",
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Thread resource.",
 *                         "example": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
 *                     }
 *                 }
 *             }
 *         },
 *         "delete": {
 *             "access_control": "object.getAuthor() == user",
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Thread resource.",
 *                         "example": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
 *                     }
 *                 }
 *             }
 *         }
 *     },
 * )
 * @ApiFilter(SearchFilter::class, properties={"answer.idea": "exact"})
 *
 * @ORM\Table(name="ideas_workshop_thread",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="threads_uuid_unique", columns="uuid")
 *     },
 *     indexes={
 *         @ORM\Index(name="idea_workshop_thread_status_idx", columns={"status"})
 *     }
 * )
 * @ORM\Entity
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @Algolia\Index(autoIndex=false)
 */
class Thread extends BaseComment implements AuthorInterface, ReportableInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="Answer", inversedBy="threads")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull
     *
     * @SymfonySerializer\Groups("thread_comment_read")
     */
    private $answer;

    /**
     * @ORM\OneToMany(targetEntity="ThreadComment", mappedBy="thread", cascade={"remove"}, orphanRemoval=true)
     * @ApiSubresource
     */
    private $comments;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->createdAt = new \DateTime();
        $this->comments = new ArrayCollection();
    }

    public static function create(
        UuidInterface $uuid,
        string $content,
        Adherent $author,
        Answer $answer,
        string $status = ThreadCommentStatusEnum::POSTED,
        \DateTime $createdAt = null
    ): self {
        $thread = new static($uuid);
        $thread->content = $content;
        $thread->author = $author;
        $thread->answer = $answer;
        $thread->status = $status;
        $thread->createdAt = $createdAt ?: new \DateTime();

        return $thread;
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

    public function getIdeaAuthor(): Adherent
    {
        return $this->getAnswer()->getIdea()->getAuthor();
    }
}

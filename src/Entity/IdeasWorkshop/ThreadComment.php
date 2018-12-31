<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AuthorInterface;
use AppBundle\Entity\Report\ReportableInterface;
use AppBundle\Report\ReportType;
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
 *         "filters": {"threadComment.thread"},
 *         "order": {"createdAt": "ASC"}
 *     },
 *     collectionOperations={
 *         "get",
 *         "post": {
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *         }
 *     },
 *     itemOperations={
 *         "get",
 *         "put_status_approve": {
 *             "method": "PUT",
 *             "path": "/thread_comments/{id}/approve",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "denormalization_context": {"api_allow_update": false},
 *             "access_control": "object.getIdeaAuthor() == user",
 *             "controller": "AppBundle\Controller\Api\ThreadCommentController::approveAction"
 *         },
 *         "put_status_report": {
 *             "method": "PUT",
 *             "path": "/thread_comments/{id}/report",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "denormalization_context": {"api_allow_update": false},
 *             "access_control": "is_granted('ROLE_ADHERENT') && object.getAuthor() != user",
 *             "controller": "AppBundle\Controller\Api\ThreadCommentController::reportAction"
 *         },
 *         "delete": {"access_control": "object.getAuthor() == user"}
 *     },
 * )
 *
 * @ORM\Table(name="ideas_workshop_comment",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="threads_comments_uuid_unique", columns="uuid")
 *     },
 *     indexes={
 *         @ORM\Index(name="idea_workshop_thread_comments_status_idx", columns={"status"})
 *     }
 * )
 * @ORM\Entity
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @Algolia\Index(autoIndex=false)
 */
class ThreadComment extends BaseComment implements AuthorInterface, ReportableInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="Thread", inversedBy="comments")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull
     *
     * @SymfonySerializer\Groups("thread_comment_read")
     */
    private $thread;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function create(
        UuidInterface $uuid,
        string $content,
        Adherent $author,
        Thread $thread,
        string $status = ThreadCommentStatusEnum::POSTED,
        \DateTime $createdAt = null
    ): self {
        $threadComment = new static($uuid);
        $threadComment->content = $content;
        $threadComment->author = $author;
        $threadComment->thread = $thread;
        $threadComment->status = $status;
        $threadComment->createdAt = $createdAt ?: new \DateTime();

        return $threadComment;
    }

    public function getThread(): Thread
    {
        return $this->thread;
    }

    public function setThread(Thread $thread): void
    {
        $this->thread = $thread;
    }

    public function getReportType(): string
    {
        return ReportType::IDEAS_WORKSHOP_THREAD_COMMENT;
    }

    public function getIdeaAuthor(): Adherent
    {
        return $this->getThread()->getIdeaAuthor();
    }
}

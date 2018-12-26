<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AuthorInterface;
use AppBundle\Entity\Report\ReportableInterface;
use AppBundle\Report\ReportType;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"thread_comment_read"}
 *         },
 *         "filters": {"threadComment.thread"},
 *         "order": {"createdAt": "ASC"}
 *     },
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 * )
 *
 * @ORM\Table(name="ideas_workshop_comment")
 *
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class ThreadComment extends BaseComment implements AuthorInterface, ReportableInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="Thread", inversedBy="comments")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @SymfonySerializer\Groups("thread_comment_read")
     */
    private $thread;

    public function __construct(
        UuidInterface $uuid,
        string $content,
        Adherent $author,
        Thread $thread,
        string $status = ThreadCommentStatusEnum::POSTED,
        \DateTime $createdAt = null
    ) {
        parent::__construct($uuid, $content, $author, $status);

        $this->thread = $thread;
        $this->createdAt = $createdAt;
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
}

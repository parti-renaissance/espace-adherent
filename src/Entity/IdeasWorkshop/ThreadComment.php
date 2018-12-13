<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AuthorInterface;
use Doctrine\ORM\Mapping as ORM;
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
class ThreadComment extends BaseComment implements AuthorInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="Thread", inversedBy="comments")
     *
     * @SymfonySerializer\Groups("thread_comment_read")
     */
    private $thread;

    public function __construct(
        string $content,
        Adherent $author,
        Thread $thread,
        string $status = ThreadCommentStatusEnum::POSTED,
        \DateTime $createdAt = null
    ) {
        $this->content = $content;
        $this->author = $author;
        $this->thread = $thread;
        $this->status = $status;
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
}

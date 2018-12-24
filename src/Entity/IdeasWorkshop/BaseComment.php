<?php

namespace AppBundle\Entity\IdeasWorkshop;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\Entity\EntitySoftDeletableTrait;
use AppBundle\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseComment
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;
    use EntityThreadCommentStatusTrait;

    /**
     * @ORM\Column(type="text")
     *
     * @SymfonySerializer\Groups("thread_comment_read")
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @SymfonySerializer\Groups("thread_comment_read")
     */
    protected $author;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public static function getVisibleStatuses(): array
    {
        return ThreadCommentStatusEnum::VISIBLE_STATUSES;
    }
}

<?php

namespace AppBundle\Entity\IdeasWorkshop;

use ApiPlatform\Core\Annotation\ApiProperty;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\EntitySoftDeletableTrait;
use AppBundle\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseComment
{
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;

    /**
     * @ApiProperty(identifier=false)
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ApiProperty(identifier=true)
     *
     * @ORM\Column(type="uuid")
     *
     * @SymfonySerializer\Groups({"thread_comment_read", "thread_list_read", "idea_read"})
     */
    protected $uuid;

    /**
     * @ORM\Column(type="text")
     *
     * @SymfonySerializer\Groups({"thread_comment_read", "thread_list_read", "idea_read"})
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @SymfonySerializer\Groups({"thread_comment_read", "thread_list_read", "idea_read"})
     */
    protected $author;

    /**
     * @ORM\Column(type="boolean")
     *
     * @SymfonySerializer\Groups({"thread_comment_read", "thread_list_read", "thread_approval", "thread_comment_approval", "idea_read"})
     */
    protected $approved = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
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

    public function isApproved(): bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): void
    {
        $this->approved = $approved;
    }
}

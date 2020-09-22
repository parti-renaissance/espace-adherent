<?php

namespace App\Entity;

use App\Validator\WysiwygLength as AssertWysiwygLength;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractFeedItem
{
    use EntityIdentityTrait;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     */
    protected $author;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     * @AssertWysiwygLength(
     *     min=10,
     *     max=6000,
     *     minMessage="common.message.min_length",
     *     maxMessage="common.message.max_length"
     * )
     */
    protected $content;

    /**
     * @var \DateTime|\DateTime|null
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    public function __construct(Adherent $author, string $content, string $createdAt = 'now')
    {
        $this->uuid = Uuid::uuid4();
        $this->author = $author;
        $this->content = $content;
        $this->createdAt = new \DateTime($createdAt);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getAuthorFirstName(): string
    {
        return $this->author->getFirstName();
    }
}

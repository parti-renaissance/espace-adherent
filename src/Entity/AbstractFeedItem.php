<?php

declare(strict_types=1);

namespace App\Entity;

use App\Validator\WysiwygLength as AssertWysiwygLength;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass]
abstract class AbstractFeedItem
{
    use EntityIdentityTrait;

    /**
     * @var Adherent
     */
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    protected $author;

    #[AssertWysiwygLength(min: 10, max: 6000, minMessage: 'common.message.min_length', maxMessage: 'common.message.max_length')]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    protected $content;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime')]
    protected $createdAt;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $isLocked = false;

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

    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(bool $isLocked): void
    {
        $this->isLocked = $isLocked;
    }
}

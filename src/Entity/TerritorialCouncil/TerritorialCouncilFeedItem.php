<?php

namespace App\Entity\TerritorialCouncil;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\TerritorialCouncilFeedItemRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class TerritorialCouncilFeedItem
{
    use EntityIdentityTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncil")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $territorialCouncil;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     */
    private $author;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @var \DateTime|\DateTime|null
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(
        TerritorialCouncil $territorialCouncil,
        Adherent $author,
        string $content,
        string $createdAt = 'now'
    ) {
        $this->uuid = Uuid::uuid4();
        $this->territorialCouncil = $territorialCouncil;
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

    public function getTerritorialCouncil(): ?TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getAuthorFirstName(): ?string
    {
        if ($this->author instanceof Adherent) {
            return $this->author->getFirstName();
        }

        return null;
    }
}

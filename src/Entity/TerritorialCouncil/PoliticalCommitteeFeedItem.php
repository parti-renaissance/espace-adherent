<?php

namespace App\Entity\TerritorialCouncil;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\PoliticalCommitteeFeedItemRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class PoliticalCommitteeFeedItem
{
    use EntityIdentityTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\PoliticalCommittee")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $politicalCommittee;

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
        PoliticalCommittee $politicalCommittee,
        Adherent $author,
        string $content,
        string $createdAt = 'now'
    ) {
        $this->uuid = Uuid::uuid4();
        $this->politicalCommittee = $politicalCommittee;
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

    public function getPoliticalCommittee(): PoliticalCommittee
    {
        return $this->politicalCommittee;
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

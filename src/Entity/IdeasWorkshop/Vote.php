<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AuthorInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"vote_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"vote_write"}
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/ideas-workshop/votes",
 *         },
 *         "post": {
 *             "path": "/ideas-workshop/votes",
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *         }
 *     },
 *     itemOperations={
 *         "get": {"path": "/ideas-workshop/votes/{id}"},
 *         "delete": {
 *             "path": "/ideas-workshop/votes/{id}",
 *             "access_control": "object.getAuthor() == user"
 *         }
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VoteRepository")
 * @ORM\Table(name="ideas_workshop_vote")
 * @ORM\EntityListeners({"AppBundle\EntityListener\VoteListener"})
 *
 * @UniqueEntity(fields={"idea", "author", "type"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class Vote implements AuthorInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @SymfonySerializer\Groups({"idea_list_read", "vote_read"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Idea", inversedBy="votes")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull
     *
     * @SymfonySerializer\Groups({"idea_list_read", "vote_read", "vote_write"})
     */
    private $idea;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull
     *
     * @SymfonySerializer\Groups({"idea_list_read", "vote_read", "vote_write"})
     */
    private $author;

    /**
     * @ORM\Column(length=10)
     *
     * @Assert\Choice(callback={"AppBundle\Entity\IdeasWorkshop\VoteTypeEnum", "toArray"})
     *
     * @SymfonySerializer\Groups({"idea_list_read", "vote_read", "vote_write"})
     */
    private $type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdea(): Idea
    {
        return $this->idea;
    }

    public function setIdea(Idea $idea): void
    {
        $this->idea = $idea;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}

<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\Entity\Adherent;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="ideas_workshop_vote")
 *
 * @UniqueEntity(fields={"idea", "adherent", "type"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class Vote
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Idea", inversedBy="votes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $idea;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $adherent;

    /**
     * @ORM\Column(length=10)
     *
     * @Assert\Choice(callback={"AppBundle\Entity\IdeasWorkshop\VoteTypeEnum", "toArray"})
     */
    private $type;

    public function __construct(Idea $idea, Adherent $adherent, string $type)
    {
        $this->idea = $idea;
        $this->adherent = $adherent;
        $this->type = $type;
    }

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

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
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

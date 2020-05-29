<?php

namespace App\Entity\VotingPlatform;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Adherent;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotingPlatform\VoterRepository")
 *
 * @ORM\Table(name="voting_platform_voter")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Voter
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Adherent|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $adherent;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var VotersList[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\VotingPlatform\VotersList", mappedBy="voters")
     */
    private $votersLists;

    public function __construct(Adherent $adherent = null)
    {
        $this->adherent = $adherent;
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }
}

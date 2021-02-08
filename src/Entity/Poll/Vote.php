<?php

namespace App\Entity\Poll;

use App\Entity\Adherent;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Poll\VoteRepository")
 *
 * @ORM\Table(name="poll_vote")
 */
class Vote
{
    use EntityTimestampableTrait;

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var Choice
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Poll\Choice", inversedBy="votes")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $choice;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $adherent;

    public function __construct(Choice $choice, ?Adherent $adherent = null)
    {
        $this->choice = $choice;
        $this->adherent = $adherent;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChoice(): Choice
    {
        return $this->choice;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\Poll;

use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Poll\VoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ORM\Table(name: 'poll_vote')]
class Vote
{
    use EntityTimestampableTrait;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    protected ?int $id = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Choice::class, inversedBy: 'votes')]
    private Choice $choice;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private ?Adherent $adherent;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Device::class)]
    private ?Device $device = null;

    public function __construct(Choice $choice, Adherent $adherent)
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

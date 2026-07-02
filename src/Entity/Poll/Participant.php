<?php

declare(strict_types=1);

namespace App\Entity\Poll;

use App\Entity\Adherent;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Poll\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\Table(name: 'poll_participant')]
#[ORM\UniqueConstraint(name: 'poll_participant_poll_adherent_unique', columns: ['poll_id', 'adherent_id'])]
class Participant
{
    use EntityTimestampableTrait;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Poll::class)]
    private Poll $poll;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private Adherent $adherent;

    public function __construct(Poll $poll, Adherent $adherent)
    {
        $this->poll = $poll;
        $this->adherent = $adherent;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }
}

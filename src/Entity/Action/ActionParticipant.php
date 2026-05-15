<?php

declare(strict_types=1);

namespace App\Entity\Action;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Action\ActionParticipantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ActionParticipantRepository::class)]
#[ORM\Table(name: 'vox_action_participant')]
class ActionParticipant
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[Groups(['action_read', 'action_read_list'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $isPresent = false;

    #[ORM\ManyToOne(targetEntity: Action::class, inversedBy: 'participants')]
    public Action $action;

    #[Groups(['action_read', 'action_read_list'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne]
    public Adherent $adherent;

    public function __construct(Action $action, Adherent $adherent)
    {
        $this->uuid = Uuid::v4();
        $this->action = $action;
        $this->adherent = $adherent;
    }
}

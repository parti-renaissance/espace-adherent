<?php

namespace App\Entity\Action;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\Table(name="vox_action_participant")
 */
class ActionParticipant
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public bool $isPresent = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Action\Action", inversedBy="participants", fetch="EAGER")
     */
    public Action $action;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", fetch="EAGER")
     */
    public Adherent $adherent;

    public function __construct(Action $action, Adherent $adherent)
    {
        $this->uuid = Uuid::uuid4();
        $this->action = $action;
        $this->adherent = $adherent;
    }
}

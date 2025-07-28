<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\Committee;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class CommitteeFilter extends AbstractUserFilter
{
    #[Assert\NotBlank]
    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Committee::class)]
    private ?Committee $committee = null;

    public function __construct(?Committee $committee = null)
    {
        parent::__construct();

        $this->committee = $committee;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }
}

<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\Committee;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class CommitteeFilter extends AbstractUserFilter
{
    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee")
     *
     * @Assert\NotBlank
     */
    private $committee;

    public function __construct(Committee $committee = null)
    {
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

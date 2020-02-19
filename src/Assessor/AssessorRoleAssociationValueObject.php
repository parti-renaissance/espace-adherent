<?php

namespace AppBundle\Assessor;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\VotePlace;
use AppBundle\Validator\ValidAdherentForVotePlace;

/**
 * @ValidAdherentForVotePlace
 */
class AssessorRoleAssociationValueObject
{
    /**
     * @var VotePlace
     */
    private $votePlace;

    /**
     * @var Adherent|null
     */
    private $adherent;

    public function __construct(VotePlace $votePlace, Adherent $adherent = null)
    {
        $this->votePlace = $votePlace;
        $this->adherent = $adherent;
    }

    public function getVotePlace(): ?VotePlace
    {
        return $this->votePlace;
    }

    public function setVotePlace(?VotePlace $votePlace): void
    {
        $this->votePlace = $votePlace;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }
}

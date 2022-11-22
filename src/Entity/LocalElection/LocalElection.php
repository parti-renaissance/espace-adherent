<?php

namespace App\Entity\LocalElection;

use App\Entity\VotingPlatform\Designation\AbstractElectionEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class LocalElection extends AbstractElectionEntity
{
    public function getLabel(): string
    {
        if (!$designation = $this->getDesignation()) {
            return '';
        }

        return sprintf(
            '%s (%s)',
            $designation->getLabel(),
            implode(', ', $designation->getZonesCodes())
        );
    }

    public function __toString(): string
    {
        return (string) $this->designation?->getLabel();
    }
}

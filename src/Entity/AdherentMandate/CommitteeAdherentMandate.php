<?php

namespace App\Entity\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\Committee;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentMandate\CommitteeAdherentMandateRepository")
 */
class CommitteeAdherentMandate extends AbstractAdherentMandate
{
    public function __construct(
        Adherent $adherent,
        string $gender,
        Committee $committee,
        \DateTime $beginAt,
        string $quality = null,
        bool $provisional = false,
        \DateTime $finishAt = null
    ) {
        parent::__construct($adherent, $gender, $beginAt, $finishAt);

        $this->committee = $committee;
        $this->quality = $quality;
        $this->provisional = $provisional;
    }

    public static function createFromMandate(CommitteeAdherentMandate $mandate, Adherent $adherent): self
    {
        return new CommitteeAdherentMandate(
            $adherent,
            $mandate->getGender(),
            $mandate->getCommittee(),
            new \DateTime(),
            $mandate->getQuality(),
            $mandate->isProvisional()
        );
    }

    public function isProvisional(): bool
    {
        return $this->provisional;
    }

    public function setProvisional(bool $provisional): void
    {
        $this->provisional = $provisional;
    }

    public function end(\DateTime $now, string $reason = null): void
    {
        $this->finishAt = $now;
        $this->setReason($reason);
    }

    public function isSupervisor(): bool
    {
        return CommitteeMandateQualityEnum::SUPERVISOR === $this->quality;
    }
}

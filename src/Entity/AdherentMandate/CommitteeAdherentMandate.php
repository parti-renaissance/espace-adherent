<?php

namespace App\Entity\AdherentMandate;

use App\Admin\Committee\CommitteeAdherentMandateTypeEnum;
use App\Committee\CommitteeAdherentMandateCommand;
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

    public static function createFromCommand(CommitteeAdherentMandateCommand $command): self
    {
        return new CommitteeAdherentMandate(
            $command->getAdherent(),
            $command->getGender(),
            $command->getCommittee(),
            new \DateTime(),
            $command->getQuality(),
            $command->isProvisional()
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

    public function getType(): ?string
    {
        if ($this->isSupervisor()) {
            if ($this->isProvisional()) {
                return $this->isFemale()
                    ? CommitteeAdherentMandateTypeEnum::PROVISIONAL_SUPERVISOR_FEMALE
                    : CommitteeAdherentMandateTypeEnum::PROVISIONAL_SUPERVISOR_MALE;
            } else {
                return $this->isFemale()
                    ? CommitteeAdherentMandateTypeEnum::SUPERVISOR_FEMALE
                    : CommitteeAdherentMandateTypeEnum::SUPERVISOR_MALE;
            }
        } elseif (!$this->getQuality()) {
            return $this->isFemale()
                ? CommitteeAdherentMandateTypeEnum::ELECTED_ADHERENT_FEMALE
                : CommitteeAdherentMandateTypeEnum::ELECTED_ADHERENT_MALE;
        }

        return null;
    }
}

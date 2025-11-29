<?php

declare(strict_types=1);

namespace App\Entity\AdherentMandate;

use App\Admin\Committee\CommitteeAdherentMandateTypeEnum;
use App\Committee\DTO\CommitteeAdherentMandateCommand;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommitteeAdherentMandateRepository::class)]
class CommitteeAdherentMandate extends AbstractAdherentMandate
{
    public static function createFromCommand(CommitteeAdherentMandateCommand $command): self
    {
        $mandate = new self(
            $command->getAdherent(),
            $command->getGender(),
            new \DateTime(),
            null,
            $command->getQuality(),
            $command->isProvisional()
        );

        $mandate->setCommittee($command->getCommittee());

        return $mandate;
    }

    public static function createForCommittee(
        Committee $committee,
        Adherent $adherent,
        ?\DateTime $beginDate = null,
        ?\DateTime $finishDate = null,
        ?string $quality = null,
        bool $isProvisional = false,
    ): self {
        $mandate = new self($adherent, $adherent->getGender(), $beginDate ?? new \DateTime(), $finishDate, $quality, $isProvisional);
        $mandate->setCommittee($committee);

        return $mandate;
    }

    public function isProvisional(): bool
    {
        return $this->provisional;
    }

    public function setProvisional(bool $provisional): void
    {
        $this->provisional = $provisional;
    }

    public function end(\DateTime $now, ?string $reason = null): void
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

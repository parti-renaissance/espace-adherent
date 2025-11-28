<?php

declare(strict_types=1);

namespace App\Committee\DTO;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\Committee;
use App\Validator\AdherentForCommitteeMandateReplacement as AssertAdherentValid;
use Symfony\Component\Validator\Constraints as Assert;

#[AssertAdherentValid(errorPath: 'adherent')]
class CommitteeAdherentMandateCommand
{
    /**
     * @var Adherent|null
     */
    #[Assert\NotNull(message: 'adherent_mandate.adherent.empty')]
    protected $adherent;

    /**
     * @var string|null
     */
    private $gender;

    /**
     * @var \DateTime|null
     */
    protected $beginAt;

    /**
     * @var Committee
     */
    protected $committee;

    /**
     * @var string|null
     */
    protected $quality;

    /**
     * @var bool
     */
    public $provisional;

    public function __construct(Committee $committee)
    {
        $this->committee = $committee;
    }

    public static function createFromCommitteeMandate(CommitteeAdherentMandate $mandate): self
    {
        $dto = new self($mandate->getCommittee());
        $dto->gender = $mandate->getGender();
        $dto->quality = $mandate->getQuality();
        $dto->provisional = $mandate->isSupervisor();
        $dto->beginAt = new \DateTime();

        return $dto;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    public function isProvisional(): ?bool
    {
        return $this->provisional;
    }

    public function setProvisional(bool $provisional): void
    {
        $this->provisional = $provisional;
    }

    public function getBeginAt(): ?\DateTime
    {
        return $this->beginAt;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function setQuality(?string $quality): void
    {
        $this->quality = $quality;
    }

    public function isSupervisor(): bool
    {
        return CommitteeMandateQualityEnum::SUPERVISOR === $this->quality;
    }
}

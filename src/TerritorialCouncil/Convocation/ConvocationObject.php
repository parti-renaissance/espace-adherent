<?php

namespace App\TerritorialCouncil\Convocation;

use App\Address\Address;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\TerritorialCouncil\Designation\DesignationVoteModeEnum;
use App\Validator\WysiwygLength;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Assert\Expression(
 *     "(this.getTerritorialCouncil() && !this.getPoliticalCommittee()) || (!this.getTerritorialCouncil() && this.getPoliticalCommittee())",
 *     message="Vous devez choisir soit un conseil territorial soit un comité politique."
 * )
 * @Assert\Expression("this.getMeetingEndDate() > this.getMeetingStartDate()", message="La date de fin ne peut pas être inférieure à la date de début.")
 */
class ConvocationObject
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices=App\TerritorialCouncil\Designation\DesignationVoteModeEnum::ALL)
     */
    private $mode = DesignationVoteModeEnum::VOTE_MODE_ONLINE;

    /**
     * @var Address|null
     *
     * @Assert\Valid
     */
    private $address;

    /**
     * @var string|null
     *
     * @Assert\Url
     */
    private $meetingUrl;

    /**
     * @var \DateTime|null
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $meetingStartDate;

    /**
     * @var \DateTime|null
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $meetingEndDate;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @WysiwygLength(max=2000)
     */
    private $description;

    /**
     * @var TerritorialCouncil|null
     */
    private $territorialCouncil;

    /**
     * @var PoliticalCommittee|null
     */
    private $politicalCommittee;

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): void
    {
        $this->mode = $mode;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): void
    {
        $this->address = $address;
    }

    public function getMeetingStartDate(): ?\DateTime
    {
        return $this->meetingStartDate;
    }

    public function setMeetingStartDate(?\DateTime $meetingStartDate): void
    {
        $this->meetingStartDate = $meetingStartDate;
    }

    public function getMeetingEndDate(): ?\DateTime
    {
        return $this->meetingEndDate;
    }

    public function setMeetingEndDate(?\DateTime $meetingEndDate): void
    {
        $this->meetingEndDate = $meetingEndDate;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isMeetingMode(): bool
    {
        return DesignationVoteModeEnum::VOTE_MODE_MEETING === $this->mode;
    }

    public function getMeetingUrl(): ?string
    {
        return $this->meetingUrl;
    }

    public function setMeetingUrl(?string $meetingUrl): void
    {
        $this->meetingUrl = $meetingUrl;
    }

    public function getTerritorialCouncil(): ?TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function setTerritorialCouncil(?TerritorialCouncil $territorialCouncil): void
    {
        $this->territorialCouncil = $territorialCouncil;
    }

    public function getPoliticalCommittee(): ?PoliticalCommittee
    {
        return $this->politicalCommittee;
    }

    public function setPoliticalCommittee(?PoliticalCommittee $politicalCommittee): void
    {
        $this->politicalCommittee = $politicalCommittee;
    }

    public function isOnlineMode(): bool
    {
        return DesignationVoteModeEnum::VOTE_MODE_ONLINE === $this->mode;
    }

    /**
     * @Assert\IsTrue(message="La date de début doit être minimum dans 7 jours pour le Conseil territorial ou 5 jours pour le Comité politique")
     */
    public function isValid(): bool
    {
        if ($this->territorialCouncil) {
            return $this->meetingStartDate && $this->meetingStartDate > new \DateTime('+7 days');
        }

        return $this->meetingStartDate && $this->meetingStartDate > new \DateTime('+5 days');
    }
}

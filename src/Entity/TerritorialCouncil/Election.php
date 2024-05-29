<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\EntityPostAddressTrait;
use App\Entity\TerritorialCouncil\ElectionPoll\Poll;
use App\Entity\VotingPlatform\Designation\AbstractElectionEntity;
use App\Geocoder\GeoPointInterface;
use App\Repository\TerritorialCouncil\ElectionRepository;
use App\TerritorialCouncil\Designation\DesignationVoteModeEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'territorial_council_election')]
#[ORM\Entity(repositoryClass: ElectionRepository::class)]
class Election extends AbstractElectionEntity implements GeoPointInterface
{
    use EntityPostAddressTrait;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $meetingStartDate;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $meetingEndDate;

    /**
     * @var TerritorialCouncil
     */
    #[ORM\ManyToOne(targetEntity: TerritorialCouncil::class, inversedBy: 'elections')]
    private $territorialCouncil;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $electionMode;

    /**
     * @var Poll|null
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'election', targetEntity: Poll::class, cascade: ['all'], orphanRemoval: true)]
    private $electionPoll;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $meetingUrl;

    public function getTerritorialCouncil(): TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function setTerritorialCouncil(TerritorialCouncil $territorialCouncil): void
    {
        $this->territorialCouncil = $territorialCouncil;
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

    public function getElectionMode(): ?string
    {
        return $this->electionMode;
    }

    public function setElectionMode(?string $electionMode): void
    {
        $this->electionMode = $electionMode;
    }

    public function isOnlineMode(): bool
    {
        return DesignationVoteModeEnum::VOTE_MODE_ONLINE === $this->electionMode;
    }

    public function setElectionPoll(?Poll $electionPoll): void
    {
        $this->electionPoll = $electionPoll;
    }

    public function getMeetingUrl(): ?string
    {
        return $this->meetingUrl;
    }

    public function setMeetingUrl(?string $meetingUrl): void
    {
        $this->meetingUrl = $meetingUrl;
    }

    public function getElectionPoll(): ?Poll
    {
        return $this->electionPoll;
    }
}

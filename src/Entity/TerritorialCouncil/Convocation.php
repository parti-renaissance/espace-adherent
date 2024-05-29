<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\TerritorialCouncil\ConvocationRepository;
use App\TerritorialCouncil\Designation\DesignationVoteModeEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Table(name: 'territorial_council_convocation')]
#[ORM\Entity(repositoryClass: ConvocationRepository::class)]
class Convocation
{
    use EntityIdentityTrait;
    use EntityPostAddressTrait;
    use EntityTimestampableTrait;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime')]
    private $meetingStartDate;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime')]
    private $meetingEndDate;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text')]
    private $description;

    /**
     * @var string|null
     */
    #[ORM\Column]
    private $mode = DesignationVoteModeEnum::VOTE_MODE_ONLINE;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $meetingUrl;

    /**
     * @var TerritorialCouncil|null
     */
    #[ORM\ManyToOne(targetEntity: TerritorialCouncil::class)]
    private $territorialCouncil;

    /**
     * @var PoliticalCommittee|null
     */
    #[ORM\ManyToOne(targetEntity: PoliticalCommittee::class)]
    private $politicalCommittee;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    protected $createdBy;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getEntity(): InstanceEntityInterface
    {
        return $this->territorialCouncil ?? $this->politicalCommittee;
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

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): void
    {
        $this->mode = $mode;
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

    public function getCreatedBy(): ?Adherent
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Adherent $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function isOnlineMode(): bool
    {
        return DesignationVoteModeEnum::VOTE_MODE_ONLINE === $this->mode;
    }
}

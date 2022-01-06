<?php

namespace App\Entity\Pap;

use App\Entity\Adherent;
use App\Entity\AuthorInterface;
use App\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pap\BuildingEventRepository")
 * @ORM\Table(name="pap_building_event")
 */
class BuildingEvent implements AuthorInterface
{
    use EntityIdentityTrait;

    /**
     * @ORM\Column(length=25)
     *
     * @Assert\NotBlank
     * @Assert\Choice(
     *     callback={"App\Pap\BuildingEventActionEnum", "toArray"},
     *     strict=true
     * )
     *
     * @Groups({"pap_building_event_write"})
     */
    private ?string $action = null;

    /**
     * @ORM\Column(length=25)
     *
     * @Assert\NotBlank
     * @Assert\Choice(
     *     callback={"App\Pap\BuildingEventTypeEnum", "toArray"},
     *     strict=true
     * )
     *
     * @Groups({"pap_building_event_write"})
     */
    private ?string $type = null;

    /**
     * @ORM\Column(length=50, nullable=true)
     *
     * @Groups({"pap_building_event_write"})
     */
    private ?string $identifier = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Building")
     *
     * @Assert\NotNull
     */
    private Building $building;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Campaign")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotNull
     *
     * @Groups({"pap_building_event_write"})
     */
    private ?Campaign $campaign = null;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent", fetch="EAGER")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $author;

    public function __construct(
        Building $building,
        Campaign $campaign = null,
        string $action = null,
        string $type = null,
        ?string $identifier = null
    ) {
        $this->uuid = Uuid::uuid4();
        $this->building = $building;
        $this->campaign = $campaign;
        $this->action = $action;
        $this->type = $type;
        $this->identifier = $identifier;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier = null): void
    {
        $this->identifier = $identifier;
    }

    public function getBuilding(): Building
    {
        return $this->building;
    }

    public function setBuilding(Building $building): void
    {
        $this->building = $building;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(?Adherent $author): void
    {
        $this->author = $author;
    }
}

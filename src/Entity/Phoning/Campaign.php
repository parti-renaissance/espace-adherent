<?php

namespace App\Entity\Phoning;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Audience\AudienceSnapshot;
use App\Entity\EntityAdministratorTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Team\Team;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Phoning\CampaignRepository")
 * @ORM\Table(name="phoning_campaign")
 *
 * @ApiResource(
 *     collectionOperations={
 *         "get_my_phoning_campaigns": {
 *             "method": "GET",
 *             "path": "/v3/phoning_campaigns/mine",
 *             "access_control": "is_granted('ROLE_PHONING')",
 *             "controller": "App\Controller\Api\Phoning\MyPhoningCampaignsController",
 *             "normalization_context": {"groups": {"phoning_campaign_read"}},
 *         }
 *     },
 * )
 */
class Campaign
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorTrait;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     *
     * @Groups({"phoning_campaign_read"})
     */
    private $title;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank
     * @Assert\GreaterThan(value="0")
     *
     * @Groups({"phoning_campaign_read"})
     */
    private $goal;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     *
     * @Groups({"phoning_campaign_read"})
     */
    private $finishAt;

    /**
     * @var Team|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Team\Team")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    private $team;

    /**
     * @var AudienceSnapshot|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Audience\AudienceSnapshot", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     */
    private $audience;

    public function __construct(
        UuidInterface $uuid = null,
        string $title = null,
        Team $team = null,
        AudienceSnapshot $audience = null,
        int $goal = null,
        \DateTimeInterface $finishAt = null
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->title = $title;
        $this->team = $team;
        $this->audience = $audience;
        $this->goal = $goal;
        $this->finishAt = $finishAt;
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getGoal(): ?int
    {
        return $this->goal;
    }

    public function setGoal(?int $goal): void
    {
        $this->goal = $goal;
    }

    public function getFinishAt(): ?\DateTime
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTime $finishAt): void
    {
        $this->finishAt = $finishAt;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }

    public function getAudience(): ?AudienceSnapshot
    {
        return $this->audience;
    }

    public function setAudience(AudienceSnapshot $audience): void
    {
        $this->audience = $audience;
    }
}

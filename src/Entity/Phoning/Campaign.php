<?php

namespace App\Entity\Phoning;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\Audience\AudienceSnapshot;
use App\Entity\EntityAdministratorTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Jecoute\Survey;
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
 *     attributes={
 *         "access_control": "is_granted('ROLE_PHONING_CAMPAIGN_MEMBER')",
 *         "normalization_context": {
 *             "iri": true,
 *             "groups": {"phoning_campaign_read"},
 *         },
 *     },
 *     itemOperations={
 *         "start_campaign_for_one_adherent": {
 *             "method": "POST",
 *             "path": "/v3/phoning_campaigns/{uuid}/start",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "controller": "App\Controller\Api\Phoning\StartCampaignController",
 *             "defaults": {"_api_receive": false},
 *         },
 *     },
 *     collectionOperations={
 *         "get_my_phoning_campaigns_scores": {
 *             "method": "GET",
 *             "path": "/v3/phoning_campaigns/scores",
 *             "controller": "App\Controller\Api\Phoning\CampaignsScoresController",
 *         },
 *     },
 *     subresourceOperations={
 *         "survey_get_subresource": {
 *             "method": "GET",
 *             "path": "/v3/phoning_campaigns/{id}/survey",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
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

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Jecoute\Survey")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     *
     * @ApiSubresource
     */
    private $survey;

    public function __construct(
        UuidInterface $uuid = null,
        string $title = null,
        Team $team = null,
        AudienceSnapshot $audience = null,
        Survey $survey = null,
        int $goal = null,
        \DateTimeInterface $finishAt = null
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->title = $title;
        $this->team = $team;
        $this->audience = $audience;
        $this->survey = $survey;
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

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(Survey $survey): void
    {
        $this->survey = $survey;
    }

    public function isFinished(): bool
    {
        return $this->finishAt <= new \DateTime();
    }
}

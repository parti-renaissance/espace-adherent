<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\EntityAdministratorTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\IndexableEntityInterface;
use App\Entity\Jecoute\Survey;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pap\CampaignRepository")
 * @ORM\Table(name="pap_campaign")
 *
 * @ORM\EntityListeners({"App\EntityListener\AlgoliaIndexListener"})
 *
 * @ApiResource(
 *     shortName="PapCampaign",
 *     attributes={
 *         "order": {"createdAt": "DESC"},
 *         "pagination_client_enabled": true,
 *         "access_control": "is_granted('IS_FEATURE_GRANTED', 'pap') or (is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER'))",
 *         "normalization_context": {
 *             "iri": true,
 *             "groups": {"pap_campaign_read"},
 *         },
 *         "denormalization_context": {
 *             "groups": {"pap_campaign_write"}
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
 *         "put": {
 *             "path": "/v3/pap_campaigns/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'pap')",
 *             "normalization_context": {"groups": {"pap_campaign_read_after_write"}},
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns",
 *         },
 *         "post": {
 *             "path": "/v3/pap_campaigns",
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'pap')",
 *             "normalization_context": {"groups": {"pap_campaign_read_after_write"}},
 *         },
 *         "get_kpi": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns/kpi",
 *             "controller": "App\Controller\Api\Pap\GetPapCampaignsKpiController",
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'pap')",
 *         },
 *     },
 *     subresourceOperations={
 *         "survey_get_subresource": {
 *             "method": "GET",
 *             "path": "/v3/pap_campaigns/{id}/survey",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
 *     },
 * )
 */
class Campaign implements IndexableEntityInterface
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
     * @Assert\Length(max=255)
     *
     * @Groups({"pap_campaign_read", "pap_campaign_write", "pap_campaign_read_after_write"})
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"pap_campaign_read", "pap_campaign_write", "pap_campaign_read_after_write"})
     */
    private $brief;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank
     * @Assert\GreaterThan(value="0")
     *
     * @Groups({"pap_campaign_read", "pap_campaign_write", "pap_campaign_read_after_write"})
     */
    private $goal;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\NotBlank(groups={"regular_campaign"})
     * @Assert\DateTime
     *
     * @Groups({"pap_campaign_write", "pap_campaign_read_after_write"})
     */
    private $beginAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\NotBlank(groups={"regular_campaign"})
     * @Assert\DateTime
     *
     * @Groups({"pap_campaign_read", "pap_campaign_write", "pap_campaign_read_after_write"})
     */
    private $finishAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Jecoute\Survey")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     *
     * @ApiSubresource
     *
     * @Groups({"pap_campaign_write", "pap_campaign_read_after_write"})
     */
    private $survey;

    /**
     * @var Collection|CampaignHistory[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Pap\CampaignHistory", mappedBy="campaign", fetch="EXTRA_LAZY")
     */
    private $campaignHistories;

    public function __construct(
        UuidInterface $uuid = null,
        string $title = null,
        string $brief = null,
        Survey $survey = null,
        int $goal = null,
        \DateTimeInterface $beginAt = null,
        \DateTimeInterface $finishAt = null
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->title = $title;
        $this->brief = $brief;
        $this->survey = $survey;
        $this->goal = $goal;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
        $this->campaignHistories = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getBrief(): ?string
    {
        return $this->brief;
    }

    public function setBrief(?string $brief): void
    {
        $this->brief = $brief;
    }

    public function getGoal(): ?int
    {
        return $this->goal;
    }

    public function setGoal(?int $goal): void
    {
        $this->goal = $goal;
    }

    public function getBeginAt(): ?\DateTime
    {
        return $this->beginAt;
    }

    public function setBeginAt(?\DateTime $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTime $finishAt): void
    {
        $this->finishAt = $finishAt;
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
        return null !== $this->finishAt && $this->finishAt <= new \DateTime();
    }

    public function getIndexOptions(): array
    {
        return [];
    }

    public function isIndexable(): bool
    {
        return true;
    }
}

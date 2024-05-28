<?php

namespace App\Entity\Jecoute;

use App\Entity\Adherent;
use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\Geo\Zone;
use App\Jecoute\SurveyTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Jecoute\LocalSurveyRepository")
 */
class LocalSurvey extends Survey implements EntityScopeVisibilityWithZoneInterface
{
    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     *
     * @deprecated
     */
    #[Groups(['survey_list', 'survey_read_dc'])]
    private $city;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @deprecated
     */
    private $tags = [];

    /**
     * @var Zone|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     *
     * @Assert\NotBlank
     */
    #[Groups(['survey_list', 'survey_list_dc', 'survey_read_dc', 'survey_write_dc'])]
    private $zone;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $blockedChanges = false;

    public static function create(Adherent $user): self
    {
        $survey = new self();
        $survey->setCreatedByAdherent($user);

        return $survey;
    }

    /**
     * @deprecated
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @deprecated
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @deprecated
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @deprecated
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getType(): string
    {
        return SurveyTypeEnum::LOCAL;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function hasBlockedChanges(): bool
    {
        return $this->blockedChanges;
    }

    public function setBlockedChanges(bool $blockedChanges): void
    {
        $this->blockedChanges = $blockedChanges;
    }
}

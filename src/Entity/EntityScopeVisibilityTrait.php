<?php

namespace App\Entity;

use App\Entity\Geo\Zone;
use App\Scope\ScopeVisibilityEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

trait EntityScopeVisibilityTrait
{
    /**
     * @ORM\Column(length=30)
     *
     * @Assert\NotBlank(message="scope.visibility.not_blank")
     * @Assert\Choice(choices=App\Scope\ScopeVisibilityEnum::ALL, message="scope.visibility.choice")
     *
     * @SymfonySerializer\Groups({
     *     "team_read",
     *     "team_list_read",
     *     "pap_campaign_read",
     *     "pap_campaign_read_after_write",
     *     "phoning_campaign_read",
     *     "phoning_campaign_list"
     * })
     */
    private string $visibility = ScopeVisibilityEnum::NATIONAL;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     * @ORM\JoinColumn(nullable=true)
     *
     * @SymfonySerializer\Groups({
     *     "team_read",
     *     "team_list_read",
     *     "team_write",
     *     "pap_campaign_read",
     *     "pap_campaign_write",
     *     "pap_campaign_read_after_write",
     *     "phoning_campaign_read",
     *     "phoning_campaign_list",
     *     "phoning_campaign_write"
     * })
     */
    private ?Zone $zone = null;

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->visibility = null !== $zone
            ? ScopeVisibilityEnum::LOCAL
            : ScopeVisibilityEnum::NATIONAL
        ;

        $this->zone = $zone;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function isNationalVisibility(): bool
    {
        return ScopeVisibilityEnum::NATIONAL === $this->visibility;
    }
}

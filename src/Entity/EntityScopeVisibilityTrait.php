<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Geo\Zone;
use App\Scope\ScopeVisibilityEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait EntityScopeVisibilityTrait
{
    #[Assert\Choice(choices: ScopeVisibilityEnum::ALL, message: 'scope.visibility.choice')]
    #[Assert\NotBlank(message: 'scope.visibility.not_blank')]
    #[Groups(['team_read', 'team_list_read', 'pap_campaign_read', 'pap_campaign_read_after_write', 'phoning_campaign_read', 'phoning_campaign_list', 'jecoute_news_read_dc', 'jecoute_news_read', 'formation_read', 'formation_list_read', 'general_meeting_report_read', 'general_meeting_report_list_read'])]
    #[ORM\Column(length: 30)]
    private string $visibility = ScopeVisibilityEnum::NATIONAL;

    #[Groups(['team_read', 'team_list_read', 'team_write', 'pap_campaign_read', 'pap_campaign_write', 'pap_campaign_read_after_write', 'phoning_campaign_read', 'phoning_campaign_list', 'phoning_campaign_write', 'jecoute_news_read_dc', 'jecoute_news_write', 'formation_read', 'formation_list_read', 'formation_write', 'general_meeting_report_list_read', 'general_meeting_report_read', 'general_meeting_report_write'])]
    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    private ?Zone $zone = null;

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
        $this->visibility = $zone && !$zone->isFrance() ? ScopeVisibilityEnum::LOCAL : ScopeVisibilityEnum::NATIONAL;
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

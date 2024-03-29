<?php

namespace App\Entity\Geo;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\GeoData;
use App\Entity\GeoPointTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait GeoTrait
{
    use GeoPointTrait;

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     *
     * @Groups({"autocomplete"})
     *
     * @ApiProperty(identifier=false)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     *
     * @Groups({
     *     "zone_read",
     *     "department_read",
     *     "region_read",
     *     "survey_list",
     *     "survey_list_dc",
     *     "survey_read_dc",
     *     "scopes",
     *     "scope",
     *     "jecoute_news_read_dc",
     *     "audience_read",
     *     "audience_segment_read",
     *     "phoning_campaign_read",
     *     "team_read",
     *     "team_list_read",
     *     "pap_campaign_read",
     *     "pap_campaign_read_after_write",
     *     "phoning_campaign_read",
     *     "phoning_campaign_list",
     *     "read_api",
     *     "department_site_read",
     *     "department_site_read_list",
     *     "elected_representative_read",
     *     "elected_representative_list",
     *     "formation_list_read",
     *     "formation_read",
     *     "elected_mandate_read",
     *     "adherent_elect_read",
     *     "general_meeting_report_list_read",
     *     "general_meeting_report_read",
     *     "committee:read",
     *     "zone:code,type",
     *     "managed_users_list",
     *     "managed_user_read",
     *     "procuration_request_read",
     *     "procuration_request_list",
     *     "procuration_proxy_list",
     *     "procuration_matched_proxy",
     * })
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Groups({
     *     "zone_read",
     *     "department_read",
     *     "region_read",
     *     "survey_list",
     *     "survey_list_dc",
     *     "survey_read_dc",
     *     "scopes",
     *     "scope",
     *     "jecoute_news_read_dc",
     *     "audience_read",
     *     "audience_segment_read",
     *     "phoning_campaign_read",
     *     "team_read",
     *     "team_list_read",
     *     "pap_campaign_read",
     *     "pap_campaign_read_after_write",
     *     "phoning_campaign_read",
     *     "phoning_campaign_list",
     *     "department_site_read",
     *     "department_site_read_list",
     *     "elected_representative_read",
     *     "elected_representative_list",
     *     "formation_list_read",
     *     "formation_read",
     *     "elected_mandate_read",
     *     "adherent_elect_read",
     *     "general_meeting_report_list_read",
     *     "general_meeting_report_read",
     *     "committee:read",
     *     "managed_users_list",
     *     "managed_user_read",
     *     "procuration_request_read",
     *     "procuration_request_list",
     *     "procuration_proxy_list",
     *     "procuration_matched_proxy",
     * })
     */
    private $name;

    /**
     * @var GeoData|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\GeoData")
     */
    private $geoData;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->name, $this->code);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @Groups({"autocomplete"})
     */
    public function getNameCode(): string
    {
        return sprintf('%s %s', $this->name, $this->code);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function activate(bool $active = true): void
    {
        $this->active = $active;
    }

    public function getGeoData(): ?GeoData
    {
        return $this->geoData;
    }

    public function setGeoData(?GeoData $geoData): void
    {
        $this->geoData = $geoData;
    }
}

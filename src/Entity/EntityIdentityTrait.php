<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

trait EntityIdentityTrait
{
    /**
     * The unique auto incremented primary key.
     *
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     *
     * @ApiProperty(identifier=false)
     *
     * @Groups({"autocomplete", "survey_list"})
     */
    protected $id;

    /**
     * The internal primary identity key.
     *
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid", unique=true)
     *
     * @Groups({
     *     "user_profile",
     *     "approach_list_read",
     *     "adherent_elect_read",
     *     "cause_read",
     *     "zone_read",
     *     "profile_read",
     *     "poll_read",
     *     "email_template_read",
     *     "email_template_list_read",
     *     "email_template_read_restricted",
     *     "message_read_list",
     *     "message_read",
     *     "event_list_read",
     *     "event_read",
     *     "audience_read",
     *     "audience_list_read",
     *     "riposte_list_read",
     *     "riposte_read",
     *     "audience_segment_read",
     *     "adherent_message_update_filter",
     *     "message_read_content",
     *     "phoning_campaign_call_read",
     *     "phoning_campaign_history_read",
     *     "phoning_campaign_read",
     *     "phoning_campaign_read_with_score",
     *     "phoning_campaign_list",
     *     "phoning_campaign_history_read_list",
     *     "data_survey_write",
     *     "data_survey_read",
     *     "jemarche_data_survey_read",
     *     "team_read",
     *     "team_list_read",
     *     "my_team_read",
     *     "my_team_read_list",
     *     "my_team_member_read",
     *     "my_team_member_post",
     *     "pap_campaign_read",
     *     "pap_campaign_read_list",
     *     "adherent_autocomplete",
     *     "pap_campaign_history_read",
     *     "pap_campaign_history_read_list",
     *     "pap_building_read",
     *     "pap_address_list",
     *     "pap_address_read",
     *     "pap_address_voter_list",
     *     "pap_building_block_list",
     *     "pap_campaign_read_after_write",
     *     "phoning_campaign_replies_list",
     *     "pap_campaign_replies_list",
     *     "pap_vote_place_read",
     *     "pap_building_statistics_read",
     *     "survey_replies_list",
     *     "jecoute_resource_links_read",
     *     "scope",
     *     "contact_read_after_write",
     *     "contact_read",
     *     "department_site_read",
     *     "department_site_post_write",
     *     "department_site_read_list",
     *     "elected_representative_read",
     *     "elected_representative_list",
     *     "formation_read",
     *     "formation_list_read",
     *     "elected_mandate_read",
     *     "committee:list",
     *     "committee:read",
     *     "committee_election:read",
     *     "committee_candidacies_group:read",
     *     "committee_candidacy:read",
     *     "general_meeting_report_list_read",
     *     "general_meeting_report_read",
     *     "document_read",
     *     "designation_read",
     *     "designation_list",
     *     "national_event_inscription:webhook",
     * })
     *
     * @ApiProperty(
     *     identifier=true,
     *     attributes={
     *         "swagger_context": {
     *             "type": "string",
     *             "format": "uuid",
     *             "example": "b4219d47-3138-5efd-9762-2ef9f9495084"
     *         }
     *     }
     * )
     */
    protected $uuid;

    /**
     * Returns the primary key identifier.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Returns the internal unique UUID instance.
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}

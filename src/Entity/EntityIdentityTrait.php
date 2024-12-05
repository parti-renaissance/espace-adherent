<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

trait EntityIdentityTrait
{
    /**
     * The unique auto incremented primary key.
     *
     * @var int|null
     */
    #[ApiProperty(identifier: false)]
    #[Groups(['autocomplete', 'survey_list'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    protected $id;

    /**
     * The internal primary identity key.
     *
     * @var UuidInterface
     */
    #[ApiProperty(identifier: true)]
    #[Groups([
        'action_read',
        'action_read_list',
        'adherent_autocomplete',
        'adherent_elect_read',
        'adherent_message_update_filter',
        'audience_list_read',
        'audience_read',
        'committee_candidacies_group:write',
        'elected_representative_write',
        'elected_mandate_write',
        'audience_segment_read',
        'my_team_member_write',
        'cause_read',
        'committee:list',
        'committee:update_animator',
        'committee:read',
        'committee_candidacies_group:read',
        'committee_candidacy:read',
        'jecoute_news_write',
        'pap_campaign_history_write',
        'committee_election:read',
        'contact_read',
        'contact_read_after_write',
        'data_survey_read',
        'data_survey_write',
        'data_survey_write:include_survey',
        'department_site_post_write',
        'department_site_read',
        'department_site_read_list',
        'designation_list',
        'designation_read',
        'document_read',
        'elected_mandate_read',
        'elected_representative_list',
        'elected_representative_read',
        'email_template_list_read',
        'email_template_read',
        'email_template_read_restricted',
        'event_list_read',
        'event_read',
        'formation_list_read',
        'formation_read',
        'general_meeting_report_list_read',
        'general_meeting_report_read',
        'hub_items_list',
        'jecoute_resource_links_read',
        'jemarche_data_survey_read',
        'message_read',
        'message_read_content',
        'message_read_list',
        'my_team_member_post',
        'my_team_member_read',
        'my_team_read',
        'my_team_read_list',
        'national_event_inscription:webhook',
        'pap_address_list',
        'pap_address_read',
        'pap_address_voter_list',
        'pap_building_block_list',
        'pap_building_read',
        'pap_building_statistics_read',
        'pap_campaign_history_read',
        'pap_campaign_history_read_list',
        'pap_campaign_read',
        'pap_campaign_read_after_write',
        'pap_campaign_read_list',
        'pap_campaign_replies_list',
        'pap_campaign_write',
        'pap_vote_place_read',
        'phoning_campaign_call_read',
        'phoning_campaign_history_read',
        'phoning_campaign_history_read_list',
        'phoning_campaign_list',
        'phoning_campaign_read',
        'phoning_campaign_read_with_score',
        'phoning_campaign_replies_list',
        'phoning_campaign_write',
        'poll_read',
        'procuration_matched_proxy',
        'procuration_proxy_list',
        'procuration_proxy_list_request',
        'procuration_proxy_slot_read',
        'procuration_request_list',
        'procuration_request_list_proxy',
        'procuration_request_read',
        'procuration_request_slot_read',
        'profile_read',
        'riposte_list_read',
        'riposte_read',
        'scope',
        'survey_list',
        'survey_list_dc',
        'survey_read_dc',
        'survey_replies_list',
        'team_list_read',
        'team_read',
        'user_profile',
        'zone_read',
        'tax_receipt:list',
        'event_registration_list',
        'zone_based_role_read',
        'zone_based_role_write',
    ])]
    #[ORM\Column(type: 'uuid', unique: true)]
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

<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

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
     * @SymfonySerializer\Groups({"autocomplete", "survey_list"})
     */
    protected $id;

    /**
     * The internal primary identity key.
     *
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     *
     * @SymfonySerializer\Groups({
     *     "user_profile",
     *     "idea_list_read",
     *     "my_committees",
     *     "idea_thread_comment_read",
     *     "idea_read",
     *     "idea_thread_list_read",
     *     "approach_list_read",
     *     "coalition_read",
     *     "cause_read",
     *     "zone_read",
     *     "profile_read",
     *     "poll_read",
     *     "email_template_read",
     *     "email_template_list_read",
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
     *     "phoning_campaign_call_read",
     *     "phoning_campaign_history_read",
     *     "phoning_campaign_read",
     *     "phoning_campaign_read_with_score",
     *     "phoning_campaign_list",
     *     "data_survey_write",
     *     "data_survey_read",
     *     "jemarche_data_survey_read",
     *     "team_read",
     *     "team_list_read",
     *     "pap_campaign_read",
     *     "adherent_autocomplete",
     *     "pap_campaign_history_read",
     *     "pap_address_list",
     *     "pap_address_read",
     *     "pap_address_voter_list",
     *     "phoning_campaign_history_read_list",
     *     "pap_building_block_list",
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

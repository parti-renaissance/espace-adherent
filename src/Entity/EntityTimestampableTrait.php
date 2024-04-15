<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

trait EntityTimestampableTrait
{
    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     *
     * @Groups({
     *     "jecoute_news_read",
     *     "jecoute_news_read_dc",
     *     "email_template_read",
     *     "email_template_list_read",
     *     "riposte_list_read",
     *     "riposte_read",
     *     "phoning_campaign_read",
     *     "message_read_list",
     *     "pap_building_history",
     *     "pap_campaign_history_read_list",
     *     "pap_campaign_replies_list",
     *     "event_read",
     *     "event_list_read",
     *     "survey_list_dc",
     *     "committee:list",
     *     "document_read",
     *     "national_event_inscription:webhook",
     *     "procuration_request_read",
     *     "procuration_request_list",
     *     "procuration_proxy_list",
     *     "procuration_matched_proxy",
     * })
     *
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     *
     * @Groups({
     *     "phoning_campaign_read",
     *     "committee:list",
     *     "national_event_inscription:webhook",
     * })
     *
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}

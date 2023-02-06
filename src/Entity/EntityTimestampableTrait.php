<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

trait EntityTimestampableTrait
{
    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     *
     * @SymfonySerializer\Groups({
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
     *     "event_list_read_extended",
     *     "survey_list_dc",
     *     "formation_read",
     *     "formation_list_read",
     * })
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     *
     * @SymfonySerializer\Groups({"phoning_campaign_read", "formation_read"})
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

<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
class MailchimpCampaignReport implements Timestampable
{
    use TimestampableEntity;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $openTotal = 0;

    #[ORM\Column(type: 'integer')]
    private int $openUnique = 0;

    #[ORM\Column(type: 'float')]
    private float $openRate = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $lastOpen = null;

    #[ORM\Column(type: 'integer')]
    private int $clickTotal = 0;

    #[ORM\Column(type: 'integer')]
    private int $clickUnique = 0;

    #[ORM\Column(type: 'float')]
    private float $clickRate = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $lastClick = null;

    #[ORM\Column(type: 'integer')]
    private int $emailSent = 0;

    #[ORM\Column(type: 'integer')]
    private int $unsubscribed = 0;

    #[ORM\Column(type: 'float')]
    private float $unsubscribedRate = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpenTotal(): int
    {
        return $this->openTotal;
    }

    public function setOpenTotal(int $openTotal): void
    {
        $this->openTotal = $openTotal;
    }

    public function getOpenUnique(): int
    {
        return $this->openUnique;
    }

    public function setOpenUnique(int $openUnique): void
    {
        $this->openUnique = $openUnique;
    }

    public function getOpenRate(): float
    {
        return $this->openRate;
    }

    public function setOpenRate(float $openRate): void
    {
        $this->openRate = $openRate;
    }

    public function getLastOpen(): ?\DateTime
    {
        return $this->lastOpen;
    }

    public function setLastOpen(?\DateTime $lastOpen): void
    {
        $this->lastOpen = $lastOpen;
    }

    public function getClickTotal(): int
    {
        return $this->clickTotal;
    }

    public function setClickTotal(int $clickTotal): void
    {
        $this->clickTotal = $clickTotal;
    }

    public function getClickUnique(): int
    {
        return $this->clickUnique;
    }

    public function setClickUnique(int $clickUnique): void
    {
        $this->clickUnique = $clickUnique;
    }

    public function getClickRate(): float
    {
        return $this->clickRate;
    }

    public function setClickRate(float $clickRate): void
    {
        $this->clickRate = $clickRate;
    }

    public function getLastClick(): ?\DateTime
    {
        return $this->lastClick;
    }

    public function setLastClick(?\DateTime $lastClick): void
    {
        $this->lastClick = $lastClick;
    }

    public function getEmailSent(): int
    {
        return $this->emailSent;
    }

    public function setEmailSent(int $emailSent): void
    {
        $this->emailSent = $emailSent;
    }

    public function getUnsubscribed(): int
    {
        return $this->unsubscribed;
    }

    public function setUnsubscribed(int $unsubscribed): void
    {
        $this->unsubscribed = $unsubscribed;
    }

    public function getUnsubscribedRate(): float
    {
        return $this->unsubscribedRate;
    }

    public function setUnsubscribedRate(float $unsubscribedRate): void
    {
        $this->unsubscribedRate = $unsubscribedRate;
    }
}

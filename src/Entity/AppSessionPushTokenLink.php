<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
class AppSessionPushTokenLink
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: AppSession::class, inversedBy: 'pushTokenLinks')]
    public ?AppSession $appSession = null;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne]
    public ?PushToken $pushToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $lastActivityDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $unsubscribedAt = null;

    public function __construct(AppSession $appSession, PushToken $pushToken)
    {
        $this->uuid = Uuid::uuid4();
        $this->appSession = $appSession;
        $this->pushToken = $pushToken;
        $this->lastActivityDate = $this->createdAt = new \DateTime();
    }

    public function unsubscribe(\DateTime $dateTime): void
    {
        $this->pushToken->unsubscribedAt = $this->unsubscribedAt = $dateTime;
    }

    public function isSubscribed(): bool
    {
        return null === $this->unsubscribedAt;
    }
}

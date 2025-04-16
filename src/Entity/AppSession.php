<?php

namespace App\Entity;

use App\AppSession\SessionStatusEnum;
use App\Entity\OAuth\AccessToken;
use App\Entity\OAuth\Client;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
class AppSession
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne]
    public ?Adherent $adherent = null;

    #[ORM\ManyToOne]
    public ?Client $client = null;

    #[ORM\Column(enumType: SessionStatusEnum::class)]
    public SessionStatusEnum $status = SessionStatusEnum::ACTIVE;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $lastActivityDate = null;

    #[ORM\Column(nullable: true)]
    public ?string $userAgent = null;

    #[ORM\Column(nullable: true)]
    public ?string $appVersion = null;

    #[ORM\OneToMany(mappedBy: 'appSession', targetEntity: AccessToken::class)]
    private Collection $accessTokens;

    public function __construct(Adherent $adherent, Client $client)
    {
        $this->uuid = Uuid::uuid4();
        $this->adherent = $adherent;
        $this->client = $client;
        $this->lastActivityDate = new \DateTime();
        $this->accessTokens = new ArrayCollection();
    }

    public function refresh(?string $userAgent, ?string $appVersion): void
    {
        $this->lastActivityDate = new \DateTime();
        $this->userAgent = $userAgent ?: $this->userAgent;
        $this->appVersion = $appVersion ?: $this->appVersion;
    }

    public function terminate(): void
    {
        if ($this->isActive()) {
            $this->status = SessionStatusEnum::TERMINATED;
            $this->lastActivityDate = new \DateTime();
        }
    }

    public function isActive(): bool
    {
        return SessionStatusEnum::ACTIVE === $this->status;
    }
}

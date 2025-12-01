<?php

declare(strict_types=1);

namespace App\Entity;

use App\AppSession\SessionStatusEnum;
use App\AppSession\SystemEnum;
use App\Entity\OAuth\AccessToken;
use App\Entity\OAuth\Client;
use App\Repository\AppSessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: AppSessionRepository::class)]
#[ORM\Index(fields: ['status'])]
#[ORM\Index(fields: ['appSystem'])]
class AppSession implements \Stringable
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(inversedBy: 'appSessions')]
    public ?Adherent $adherent = null;

    #[ORM\ManyToOne]
    public ?Client $client = null;

    #[ORM\Column(enumType: SessionStatusEnum::class)]
    public SessionStatusEnum $status = SessionStatusEnum::ACTIVE;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $lastActivityDate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $userAgent = null;

    #[ORM\Column(nullable: true)]
    public ?string $ip = null;

    #[ORM\Column(nullable: true, enumType: SystemEnum::class)]
    public ?SystemEnum $appSystem = null;

    #[ORM\Column(nullable: true)]
    public ?string $appVersion = null;

    #[ORM\OneToMany(mappedBy: 'appSession', targetEntity: AccessToken::class)]
    private Collection $accessTokens;

    #[ORM\OneToMany(mappedBy: 'appSession', targetEntity: AppSessionPushTokenLink::class, cascade: ['persist'], fetch: 'EAGER')]
    private Collection $pushTokenLinks;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $unsubscribedAt = null;

    public function __construct(Adherent $adherent, Client $client)
    {
        $this->uuid = Uuid::uuid4();
        $this->adherent = $adherent;
        $this->client = $client;
        $this->lastActivityDate = new \DateTime();
        $this->accessTokens = new ArrayCollection();
        $this->pushTokenLinks = new ArrayCollection();
    }

    public function refresh(?string $userAgent, ?string $appVersion, ?SystemEnum $system = null, ?string $ip = null): void
    {
        $this->lastActivityDate = new \DateTime();
        $this->userAgent = $userAgent ?: $this->userAgent;
        $this->appVersion = $appVersion ?: $this->appVersion;
        $this->appSystem = $system ?: $this->appSystem;
        $this->ip = $ip ?: $this->ip;
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

    public function isSubscribed(): bool
    {
        return $this->isActive() && null === $this->unsubscribedAt && \count($this->findSubscribedPushTokenLinks()) > 0;
    }

    public function __toString(): string
    {
        return implode(' - ', array_filter([
            $this->adherent?->getFullName(),
            $this->client?->getName(),
        ]));
    }

    public function unsubscribe(): void
    {
        $this->unsubscribedAt = new \DateTime();
        array_map(fn (AppSessionPushTokenLink $link) => $link->unsubscribe($this->unsubscribedAt), $this->findSubscribedPushTokenLinks());
    }

    public function findSubscribedPushTokenLinks(): array
    {
        $links = $this->pushTokenLinks->filter(static fn (AppSessionPushTokenLink $link) => $link->isSubscribed())->toArray();
        usort($links, static fn (AppSessionPushTokenLink $a, AppSessionPushTokenLink $b) => $a->getCreatedAt() <=> $b->getCreatedAt());

        return $links;
    }

    public function addPushToken(PushToken $token): void
    {
        if (!$existingSubscribedLink = current(array_filter($this->findSubscribedPushTokenLinks(), fn (AppSessionPushTokenLink $link) => $link->pushToken->identifier === $token->identifier))) {
            $existingSubscribedLink = new AppSessionPushTokenLink($this, $token);
            $this->pushTokenLinks->add($existingSubscribedLink);
        }

        $this->lastActivityDate =
        $token->lastActivityDate =
        $existingSubscribedLink->lastActivityDate = new \DateTime();

        $this->unsubscribedAt = null;
    }

    /**
     * @return AppSessionPushTokenLink[]
     */
    public function getPushTokenLinks(): array
    {
        return $this->pushTokenLinks->toArray();
    }
}

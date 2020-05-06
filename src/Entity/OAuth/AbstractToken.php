<?php

namespace App\Entity\OAuth;

use App\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractToken
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $identifier;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $expiresAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $revokedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(UuidInterface $uuid, string $identifier, \DateTime $expiryDateTime)
    {
        $this->uuid = $uuid;
        $this->identifier = $identifier;
        $this->expiresAt = $expiryDateTime;
        $this->createdAt = new \DateTime();
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getExpiryDateTime(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromMutable($this->expiresAt);
    }

    public function getExpiryTimestamp(): int
    {
        return (int) $this->getExpiryDateTime()->format('U');
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTime('now', $this->expiresAt->getTimezone());
    }

    public function isRevoked(): bool
    {
        return null !== $this->revokedAt;
    }

    public function revoke(string $datetime = 'now'): void
    {
        if ($this->revokedAt) {
            throw new \LogicException(sprintf('Token of type "%s" and identified by "%s" has already been revoked!', \get_class($this), $this->identifier));
        }

        $this->revokedAt = new \DateTime($datetime);
    }
}

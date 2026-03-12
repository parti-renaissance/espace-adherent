<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\AdherentTokenAlreadyUsedException;
use App\Exception\AdherentTokenExpiredException;
use App\Exception\AdherentTokenMismatchException;
use App\ValueObject\SHA1;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * An abstract temporary token for Adherent.
 *
 * @phpstan-consistent-constructor
 */
#[ORM\MappedSuperclass]
abstract class AdherentToken implements AdherentExpirableTokenInterface
{
    use EntityIdentityTrait;

    #[ORM\Column(type: 'uuid')]
    private $adherentUuid;

    /**
     * @var SHA1|string
     */
    #[ORM\Column(length: 40)]
    private $value;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[Groups(['profile_read'])]
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $expiredAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $usedAt = null;

    public function __construct(UuidInterface $adherentUuid, \DateTimeImmutable $createdAt, \DateTimeImmutable $expiration, SHA1 $value)
    {
        if ($expiration <= new \DateTimeImmutable('now')) {
            throw new \InvalidArgumentException('Expiration date must be in the future.');
        }

        $this->uuid = Uuid::uuid4();
        $this->value = $value;
        $this->adherentUuid = $adherentUuid;
        $this->createdAt = $createdAt;
        $this->expiredAt = $expiration;
    }

    public static function generate(Adherent $adherent, string $lifetime = '+1 day'): static
    {
        $timestamp = new \DateTimeImmutable('now');
        $adherentUuid = clone $adherent->getUuid();

        return new static(
            $adherentUuid,
            $timestamp,
            new \DateTimeImmutable($lifetime),
            SHA1::hash($adherentUuid->toString().$timestamp->format('U.u'))
        );
    }

    public static function create(
        string $adherentUuid,
        string $hash,
        string $lifetime = '+1 day',
    ): AdherentExpirableTokenInterface {
        $timestamp = new \DateTimeImmutable('now');

        return new static(
            Uuid::fromString($adherentUuid),
            $timestamp,
            new \DateTimeImmutable($lifetime),
            SHA1::fromString($hash)
        );
    }

    public function getValue(): SHA1
    {
        if (!$this->value instanceof SHA1) {
            $this->value = SHA1::fromString($this->value);
        }

        return $this->value;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherentUuid;
    }

    public function getUsageDate(): ?\DateTimeImmutable
    {
        return $this->usedAt;
    }

    public function consume(Adherent $adherent): void
    {
        $this->validate($adherent);

        $this->usedAt = new \DateTimeImmutable('now');
    }

    public function validate(Adherent $adherent): void
    {
        if (null !== $this->usedAt) {
            throw new AdherentTokenAlreadyUsedException($this);
        }

        if (!$this->adherentUuid->equals($adherent->getUuid())) {
            throw new AdherentTokenMismatchException($this, $adherent->getUuid());
        }

        if ($this->isExpired()) {
            throw new AdherentTokenExpiredException($this);
        }
    }

    public function getExpiredAt(): \DateTimeInterface
    {
        return $this->expiredAt;
    }

    private function isExpired(): bool
    {
        return new \DateTimeImmutable('now') > $this->expiredAt;
    }

    public function invalidate(): void
    {
        $this->expiredAt = new \DateTimeImmutable('-1 second');
    }
}

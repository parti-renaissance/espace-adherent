<?php

namespace AppBundle\Entity;

use AppBundle\Exception\ActivationKeyAlreadyUsedException;
use AppBundle\Exception\ActivationKeyExpiredException;
use AppBundle\Exception\ActivationKeyMismatchException;
use AppBundle\ValueObject\SHA1;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="adherent_activation_keys", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="key_token_unique", columns="token"),
 *   @ORM\UniqueConstraint(name="key_token_account_unique", columns={"token", "adherent_uuid"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ActivationKeyRepository")
 */
final class ActivationKey
{
    use EntityIdentityTrait;

    /**
     * @ORM\Column(type="uuid")
     */
    private $adherentUuid;

    /**
     * @var SHA1|string
     *
     * @ORM\Column(length=40)
     */
    private $token;

    /**
     * @var \DateTimeImmutable|\DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTimeImmutable|\DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $expiredAt;

    /**
     * @var \DateTimeImmutable|\DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $usedAt;

    private function __construct(
        UuidInterface $uuid,
        UuidInterface $adherentUuid,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $expiration,
        SHA1 $token
    ) {
        if ($expiration <= new \DateTimeImmutable('now')) {
            throw new \InvalidArgumentException('Expiration date must be in the future.');
        }

        $this->uuid = $uuid;
        $this->token = $token;
        $this->adherentUuid = $adherentUuid;
        $this->createdAt = $createdAt;
        $this->expiredAt = $expiration;
    }

    public static function generate(UuidInterface $adherentUuid, $lifetime = '+1 day'): self
    {
        $timestamp = new \DateTimeImmutable('now');

        return new self(
            static::createUuid((string) $adherentUuid),
            $adherentUuid,
            $timestamp,
            new \DateTimeImmutable($lifetime),
            SHA1::hash($adherentUuid->toString().$timestamp->format('U'))
        );
    }

    public static function createUuid(string $adherentUuid): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, $adherentUuid);
    }

    public function getToken(): SHA1
    {
        if (!$this->token instanceof SHA1) {
            $this->token = SHA1::fromString($this->token);
        }

        return $this->token;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherentUuid;
    }

    private function isExpired(): bool
    {
        return new \DateTimeImmutable('now') > $this->expiredAt;
    }

    public function getUsageDate()
    {
        if ($this->usedAt instanceof \DateTime) {
            $this->usedAt = new \DateTimeImmutable(
                $this->usedAt->format('U'),
                $this->usedAt->getTimezone()
            );
        }

        return $this->usedAt;
    }

    public function activate(UuidInterface $adherentUuid)
    {
        if (null !== $this->usedAt) {
            throw new ActivationKeyAlreadyUsedException($this);
        }

        if (!$this->adherentUuid->equals($adherentUuid)) {
            throw new ActivationKeyMismatchException($this, $adherentUuid);
        }

        if ($this->isExpired()) {
            throw new ActivationKeyExpiredException($this);
        }

        $this->usedAt = new \DateTimeImmutable('now');
    }
}

<?php

namespace App\Entity;

use App\Exception\AdherentTokenAlreadyUsedException;
use App\Exception\AdherentTokenExpiredException;
use App\Exception\AdherentTokenMismatchException;
use App\ValueObject\SHA1;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * An abstract temporary token for Adherent.
 *
 * @ORM\MappedSuperclass
 */
abstract class AdherentToken implements AdherentExpirableTokenInterface
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
    private $value;

    /**
     * @var \DateTime|\DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime|\DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $expiredAt;

    /**
     * @var \DateTime|\DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $usedAt;

    private function __construct(
        UuidInterface $uuid,
        UuidInterface $adherentUuid,
        \DateTime $createdAt,
        \DateTime $expiration,
        SHA1 $value
    ) {
        if ($expiration <= new \DateTime('now')) {
            throw new \InvalidArgumentException('Expiration date must be in the future.');
        }

        $this->uuid = $uuid;
        $this->value = $value;
        $this->adherentUuid = $adherentUuid;
        $this->createdAt = $createdAt;
        $this->expiredAt = $expiration;
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public static function generate(Adherent $adherent, string $lifetime = '+1 day'): AdherentExpirableTokenInterface
    {
        $timestamp = new \DateTime('now');
        $adherentUuid = clone $adherent->getUuid();

        return new static(
            static::createUuid((string) $adherentUuid),
            $adherentUuid,
            $timestamp,
            new \DateTime($lifetime),
            SHA1::hash($adherentUuid->toString().$timestamp->format('U'))
        );
    }

    public static function createUuid(string $adherentUuid): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, $adherentUuid);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(): SHA1
    {
        if (!$this->value instanceof SHA1) {
            $this->value = SHA1::fromString($this->value);
        }

        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherentUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsageDate()
    {
        if ($this->usedAt instanceof \DateTime) {
            $this->usedAt = new \DateTime(
                $this->usedAt->format(\DATE_RFC2822),
                $this->usedAt->getTimezone()
            );
        }

        return $this->usedAt;
    }

    public function consume(Adherent $adherent)
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

        $this->usedAt = new \DateTime('now');
    }

    private function isExpired(): bool
    {
        return new \DateTime('now') > $this->expiredAt;
    }
}

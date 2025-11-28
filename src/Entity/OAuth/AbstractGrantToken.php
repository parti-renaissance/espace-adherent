<?php

declare(strict_types=1);

namespace App\Entity\OAuth;

use App\Entity\Adherent;
use App\Entity\Device;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\MappedSuperclass]
abstract class AbstractGrantToken extends AbstractToken
{
    #[ORM\Column(type: 'json')]
    private $scopes = [];

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Client::class)]
    private $client;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $user;

    /**
     * @var Device|null
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Device::class)]
    private $device;

    public function __construct(
        UuidInterface $uuid,
        ?Adherent $user,
        string $identifier,
        \DateTimeImmutable $expiryDateTime,
        ?Client $client = null,
        ?Device $device = null,
    ) {
        parent::__construct($uuid, $identifier, $expiryDateTime);

        $this->user = $user;
        $this->device = $device;
        $this->client = $client;
    }

    public function getUser(): ?Adherent
    {
        return $this->user;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function getClientIdentifier(): string
    {
        return (string) $this->client->getUuid();
    }

    public function getUserIdentifier(): ?string
    {
        return $this->user ? (string) $this->user->getUuid() : null;
    }

    public function getDeviceIdentifier(): ?string
    {
        return $this->device ? (string) $this->device->getIdentifier() : null;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function addScope(string $scope): void
    {
        $this->addScopes([$scope]);
    }

    public function addScopes(array $scopes): void
    {
        $this->scopes = array_unique(array_merge($this->scopes, $scopes));
    }

    public function hasScope(string $scope): bool
    {
        return \in_array($scope, $this->scopes, true);
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }
}

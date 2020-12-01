<?php

namespace App\Entity\OAuth;

use App\Entity\Adherent;
use App\Entity\Device;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractGrantToken extends AbstractToken
{
    /**
     * @ORM\Column(type="json")
     */
    private $scopes = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OAuth\Client")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @var Device|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Device")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $device;

    public function __construct(
        UuidInterface $uuid,
        ?Adherent $user,
        string $identifier,
        \DateTime $expiryDateTime,
        Client $client = null,
        Device $device = null
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

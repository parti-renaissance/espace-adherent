<?php

namespace App\Entity\OAuth;

use App\Entity\Adherent;
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
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    public function __construct(
        UuidInterface $uuid,
        ?Adherent $user,
        string $identifier,
        \DateTime $expiryDateTime,
        Client $client = null
    ) {
        parent::__construct($uuid, $identifier, $expiryDateTime);

        $this->user = $user;
        $this->client = $client;
    }

    public function getUser(): ?Adherent
    {
        return $this->user;
    }

    public function getClientIdentifier(): string
    {
        return (string) $this->client->getUuid();
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->user->getUuid();
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

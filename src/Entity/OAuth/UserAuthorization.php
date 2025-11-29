<?php

declare(strict_types=1);

namespace App\Entity\OAuth;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\OAuth\Model\Scope;
use App\Repository\OAuth\UserAuthorizationRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserAuthorizationRepository::class)]
#[ORM\Table(name: 'user_authorizations')]
#[ORM\UniqueConstraint(name: 'user_authorizations_unique', columns: ['user_id', 'client_id'])]
#[UniqueEntity(fields: ['user', 'client'], message: 'user_authorization.non_unique')]
class UserAuthorization
{
    use EntityIdentityTrait;

    /**
     * @var Adherent
     */
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $user;

    /**
     * @var Client
     */
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', onDelete: 'RESTRICT')]
    #[ORM\ManyToOne(targetEntity: Client::class)]
    private $client;

    /**
     * @var array
     */
    #[ORM\Column(type: 'json')]
    private $scopes;

    /**
     * @param Scope[] $scopes
     *
     * @throws \LogicException $scopes does not contain the right object
     */
    public function __construct(?UuidInterface $uuid, Adherent $user, Client $client, array $scopes = [])
    {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->user = $user;
        $this->client = $client;

        $this->setScopes($scopes);
    }

    /**
     * @param Scope[] $scopes
     *
     * @throws \DomainException $scopes does not contain the right type of object
     */
    public function supportsScopes(array $scopes): bool
    {
        foreach ($scopes as $scope) {
            if (!$scope instanceof Scope) {
                throw new \DomainException(\sprintf('Instance of %s must be provided', Scope::class));
            }

            if (!\in_array($scope->getIdentifier(), $this->scopes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Scope[] $scopes
     */
    public function setScopes(array $scopes): void
    {
        $this->scopes = [];

        foreach ($scopes as $scope) {
            $this->addScope($scope);
        }
    }

    private function addScope(Scope $scope): void
    {
        $this->scopes[] = $scope->getIdentifier();
    }

    public function getClientName(): string
    {
        return $this->client->getName();
    }

    public function getClientUuid(): UuidInterface
    {
        return $this->client->getUuid();
    }

    public function doesClientNeedUserAuthorization(): bool
    {
        return $this->client->isAskUserForAuthorization();
    }

    public function belongsTo(Adherent $user): bool
    {
        return $this->user->getUuid()->toString() === $user->getUuid()->toString();
    }
}

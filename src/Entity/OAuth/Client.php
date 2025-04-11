<?php

namespace App\Entity\OAuth;

use App\AppCodeEnum;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntitySoftDeletableTrait;
use App\Entity\EntitySoftDeletedInterface;
use App\Entity\EntityTimestampableTrait;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use App\OAuth\SecretGenerator;
use App\Repository\OAuth\ClientRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use League\Bundle\OAuth2ServerBundle\Model\AbstractClient;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\Table(name: 'oauth_clients')]
class Client extends AbstractClient implements EntitySoftDeletedInterface
{
    use EntityIdentityTrait;
    use EntitySoftDeletableTrait;
    use EntityTimestampableTrait;

    #[Assert\Choice(callback: [AppCodeEnum::class, 'toArray'])]
    #[ORM\Column(nullable: true)]
    private ?string $code = null;

    #[Assert\Length(min: 10, max: 200, minMessage: 'La description doit faire au moins {{ limit }} caractères.', maxMessage: 'La description ne doit pas dépasser {{ limit }} caractères.')]
    #[ORM\Column]
    private $description;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'simple_array')]
    private $allowedGrantTypes;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $supportedScopes = [];

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private $askUserForAuthorization = true;

    /**
     * @var string[]|null
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $requestedRoles;

    public function __construct(
        ?UuidInterface $uuid = null,
        string $name = '',
        string $description = '',
        string $secret = '',
        array $allowedGrantTypes = [],
        array $redirectUris = [],
    ) {
        parent::__construct($name, $uuid ? $uuid->toString() : Uuid::uuid4()->toString(), $secret ?: SecretGenerator::generate());

        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->description = $description;
        $this->setAllowedGrantTypes($allowedGrantTypes);
        $this->setRedirectUris(...array_map(static fn (string $redirectUri) => new RedirectUri($redirectUri), $redirectUris));
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function addRedirectUri(string $redirectUri): void
    {
        if (!\in_array($redirectUri, $this->redirectUris, true)) {
            $this->redirectUris[] = $redirectUri;
        }
    }

    public function removeRedirectUri(string $redirectUri): void
    {
        if (false !== ($key = array_search($redirectUri, $this->redirectUris, true))) {
            unset($this->redirectUris[$key]);
        }
    }

    public function getRedirectUris(): array
    {
        return array_values($this->redirectUris);
    }

    public function hasRedirectUri(string $uri): bool
    {
        return \in_array($uri, $this->redirectUris, true);
    }

    public function setAllowedGrantTypes(array $allowedGrantTypes): void
    {
        foreach ($allowedGrantTypes as $grantType) {
            if (!GrantTypeEnum::isValid($grantType)) {
                throw new \DomainException(\sprintf('"%s" is not a valid grant type. Use constants defined in %s.', $grantType, GrantTypeEnum::class));
            }
        }

        $this->allowedGrantTypes = $allowedGrantTypes;
    }

    public function getAllowedGrantTypes(): array
    {
        return $this->allowedGrantTypes;
    }

    public function isAllowedGrantType(string $grantType): bool
    {
        return \in_array($grantType, $this->allowedGrantTypes, true);
    }

    public function addSupportedScope(string $scope): void
    {
        if (\in_array($scope, $this->supportedScopes, true)) {
            throw new \LogicException("$scope is already supported");
        }

        if (!Scope::isValid($scope)) {
            throw new \DomainException("$scope is not supported. Choose one from ".Scope::class);
        }

        $this->supportedScopes[] = $scope;
    }

    public function setSupportedScopes(array $supportedScopes): void
    {
        $this->supportedScopes = [];

        foreach ($supportedScopes as $scope) {
            $this->addSupportedScope($scope);
        }
    }

    public function supportsScope(string $scope): bool
    {
        return \in_array($scope, $this->supportedScopes, true);
    }

    public function getSupportedScopes(): array
    {
        return $this->supportedScopes;
    }

    public function verifySecret(string $secret): bool
    {
        return $this->getSecret() === $secret;
    }

    public function isAskUserForAuthorization(): bool
    {
        return $this->askUserForAuthorization;
    }

    public function setAskUserForAuthorization(bool $askUserForAuthorization): void
    {
        $this->askUserForAuthorization = $askUserForAuthorization;
    }

    public function getRequestedRoles(): ?array
    {
        return $this->requestedRoles;
    }

    public function setRequestedRoles(?array $requestedRoles): void
    {
        $this->requestedRoles = $requestedRoles;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }
}

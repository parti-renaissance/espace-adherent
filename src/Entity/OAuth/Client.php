<?php

declare(strict_types=1);

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
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\Table(name: 'oauth_clients')]
class Client implements \Stringable, EntitySoftDeletedInterface
{
    use EntityIdentityTrait;
    use EntitySoftDeletableTrait;
    use EntityTimestampableTrait;

    #[Assert\Length(max: 32, maxMessage: 'client.name.constraint.length.max')]
    #[ORM\Column]
    private $name;

    #[Assert\Choice(callback: [AppCodeEnum::class, 'toArray'])]
    #[ORM\Column(nullable: true)]
    private ?string $code = null;

    #[Assert\Length(min: 10, max: 200, minMessage: 'La description doit faire au moins {{ limit }} caractères.', maxMessage: 'La description ne doit pas dépasser {{ limit }} caractères.')]
    #[ORM\Column]
    private $description;

    #[Assert\Count(min: 1, minMessage: 'Veuillez spécifier au moins une adresse de redirection.')]
    #[ORM\Column(type: 'json')]
    private $redirectUris;

    #[ORM\Column]
    private $secret;

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

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $sessionEnabled = true;

    public function __construct(
        ?UuidInterface $uuid = null,
        string $name = '',
        string $description = '',
        string $secret = '',
        array $allowedGrantTypes = [],
        array $redirectUris = [],
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->name = $name;
        $this->description = $description;
        $this->secret = $secret ?: SecretGenerator::generate();
        $this->setAllowedGrantTypes($allowedGrantTypes);
        $this->redirectUris = $redirectUris;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
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
        $this->redirectUris = array_values(array_unique($this->redirectUris));
    }

    public function removeRedirectUri(string $redirectUri): void
    {
        if (false !== ($key = array_search($redirectUri, $this->redirectUris, true))) {
            unset($this->redirectUris[$key]);
        }
        $this->redirectUris = array_values(array_unique($this->redirectUris));
    }

    public function getRedirectUris(): array
    {
        return array_values($this->redirectUris);
    }

    public function hasRedirectUri(string $uri): bool
    {
        return \in_array($uri, $this->redirectUris, true);
    }

    public function getSecret(): string
    {
        return $this->secret;
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

    public function getSupportedScopes(bool $skipIntern = false): array
    {
        if ($skipIntern) {
            return array_values(array_filter($this->supportedScopes, static fn (string $scope) => !str_starts_with($scope, 'scope:')));
        }

        return $this->supportedScopes;
    }

    public function getUserScopes(bool $skipIntern = false): array
    {
        return array_diff($this->getSupportedScopes($skipIntern), [Scope::IMPERSONATOR]);
    }

    public function verifySecret(string $secret): bool
    {
        return $this->secret === $secret;
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

    public function isCadreClient(): bool
    {
        return AppCodeEnum::JEMENGAGE_WEB === $this->code;
    }
}

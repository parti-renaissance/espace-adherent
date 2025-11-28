<?php

declare(strict_types=1);

namespace App\Entity\OAuth;

use App\Entity\Adherent;
use App\Repository\OAuth\AuthorizationCodeRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: AuthorizationCodeRepository::class)]
#[ORM\Table(name: 'oauth_auth_codes')]
class AuthorizationCode extends AbstractGrantToken
{
    #[ORM\Column(type: 'text')]
    private $redirectUri;

    public function __construct(
        UuidInterface $uuid,
        Adherent $user,
        string $identifier,
        \DateTimeImmutable $expiryDateTime,
        string $redirectUri,
        ?Client $client = null,
    ) {
        parent::__construct($uuid, $user, $identifier, $expiryDateTime, $client);

        $this->redirectUri = $redirectUri;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }
}

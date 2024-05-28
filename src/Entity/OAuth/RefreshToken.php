<?php

namespace App\Entity\OAuth;

use App\Repository\OAuth\RefreshTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Table(name: 'oauth_refresh_tokens')]
#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
class RefreshToken extends AbstractToken
{
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: AccessToken::class)]
    private $accessToken;

    public function __construct(
        UuidInterface $uuid,
        AccessToken $accessToken,
        string $identifier,
        \DateTimeImmutable $expiryDateTime
    ) {
        parent::__construct($uuid, $identifier, $expiryDateTime);

        $this->accessToken = $accessToken;
    }

    public function getAccessToken(): AccessToken
    {
        return $this->accessToken;
    }
}

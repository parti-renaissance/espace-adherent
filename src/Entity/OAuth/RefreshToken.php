<?php

declare(strict_types=1);

namespace App\Entity\OAuth;

use App\Repository\OAuth\RefreshTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'oauth_refresh_tokens')]
class RefreshToken extends AbstractToken
{
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: AccessToken::class)]
    private $accessToken;

    public function __construct(
        Uuid $uuid,
        AccessToken $accessToken,
        string $identifier,
        \DateTimeImmutable $expiryDateTime,
    ) {
        parent::__construct($uuid, $identifier, $expiryDateTime);

        $this->accessToken = $accessToken;
    }

    public function getAccessToken(): AccessToken
    {
        return $this->accessToken;
    }
}

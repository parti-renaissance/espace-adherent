<?php

namespace App\Entity\OAuth;

use App\Entity\AppSession;
use App\Repository\OAuth\AccessTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessTokenRepository::class)]
#[ORM\Table(name: 'oauth_access_tokens')]
class AccessToken extends AbstractGrantToken
{
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'accessTokens')]
    public ?AppSession $appSession = null;

    public function revoke(string $datetime = 'now', bool $terminateSession = true): void
    {
        parent::revoke($datetime);

        if ($terminateSession && $this->appSession) {
            $this->appSession->terminate();
        }
    }
}

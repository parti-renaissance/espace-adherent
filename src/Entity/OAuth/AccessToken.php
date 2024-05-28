<?php

namespace App\Entity\OAuth;

use App\Repository\OAuth\AccessTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'oauth_access_tokens')]
#[ORM\Entity(repositoryClass: AccessTokenRepository::class)]
class AccessToken extends AbstractGrantToken
{
}

<?php

namespace App\Entity\OAuth;

use App\Repository\OAuth\AccessTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessTokenRepository::class)]
#[ORM\Table(name: 'oauth_access_tokens')]
class AccessToken extends AbstractGrantToken
{
}

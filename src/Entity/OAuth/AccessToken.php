<?php

namespace App\Entity\OAuth;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OAuth\AccessTokenRepository")
 * @ORM\Table(name="oauth_access_tokens")
 */
class AccessToken extends AbstractGrantToken
{
}

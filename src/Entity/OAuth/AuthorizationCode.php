<?php

namespace AppBundle\Entity\OAuth;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OAuth\AuthorizationCodeRepository")
 * @ORM\Table(name="oauth_auth_codes", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="oauth_auth_codes_uuid_unique", columns="uuid"),
 *     @ORM\UniqueConstraint(name="oauth_auth_codes_identifier_unique", columns="identifier")
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
class AuthorizationCode extends AbstractGrantToken
{
    /**
     * @ORM\Column(type="text")
     */
    private $redirectUri;

    public function __construct(
        UuidInterface $uuid,
        Adherent $user,
        string $identifier,
        \DateTime $expiryDateTime,
        string $redirectUri,
        Client $client = null
    ) {
        parent::__construct($uuid, $user, $identifier, $expiryDateTime, $client);

        $this->redirectUri = $redirectUri;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }
}

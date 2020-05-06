<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentChangeEmailTokenRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(columns={"email", "used_at", "expired_at"})
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
class AdherentChangeEmailToken extends AdherentToken
{
    /**
     * @var string|null
     *
     * @ORM\Column
     */
    private $email;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getType(): string
    {
        return 'adherent change email';
    }
}

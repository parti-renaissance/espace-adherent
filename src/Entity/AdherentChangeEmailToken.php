<?php

namespace App\Entity;

use App\Repository\AdherentChangeEmailTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table]
#[ORM\Index(columns: ['email', 'used_at', 'expired_at'])]
#[ORM\Entity(repositoryClass: AdherentChangeEmailTokenRepository::class)]
class AdherentChangeEmailToken extends AdherentToken
{
    /**
     * @var string|null
     */
    #[ORM\Column]
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

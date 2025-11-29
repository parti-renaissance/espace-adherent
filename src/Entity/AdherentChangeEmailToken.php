<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AdherentChangeEmailTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AdherentChangeEmailTokenRepository::class)]
#[ORM\Index(columns: ['email', 'used_at', 'expired_at'])]
#[ORM\Table]
class AdherentChangeEmailToken extends AdherentToken
{
    /**
     * @var string|null
     */
    #[Groups(['profile_read'])]
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

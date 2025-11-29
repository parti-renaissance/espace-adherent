<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AdherentActivationTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdherentActivationTokenRepository::class)]
#[ORM\Table(name: 'adherent_activation_keys')]
#[ORM\UniqueConstraint(name: 'adherent_activation_token_unique', columns: ['value'])]
#[ORM\UniqueConstraint(name: 'adherent_activation_token_account_unique', columns: ['value', 'adherent_uuid'])]
class AdherentActivationToken extends AdherentToken
{
    public function getType(): string
    {
        return 'adherent activation';
    }
}

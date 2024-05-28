<?php

namespace App\Entity;

use App\Repository\AdherentActivationTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'adherent_activation_keys')]
#[ORM\UniqueConstraint(name: 'adherent_activation_token_unique', columns: ['value'])]
#[ORM\UniqueConstraint(name: 'adherent_activation_token_account_unique', columns: ['value', 'adherent_uuid'])]
#[ORM\Entity(repositoryClass: AdherentActivationTokenRepository::class)]
class AdherentActivationToken extends AdherentToken
{
    public function getType(): string
    {
        return 'adherent activation';
    }
}

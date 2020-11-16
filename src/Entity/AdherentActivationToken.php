<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="adherent_activation_keys", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="adherent_activation_token_unique", columns="value"),
 *     @ORM\UniqueConstraint(name="adherent_activation_token_account_unique", columns={"value", "adherent_uuid"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\AdherentActivationTokenRepository")
 */
class AdherentActivationToken extends AdherentToken
{
    public function getType(): string
    {
        return 'adherent activation';
    }
}

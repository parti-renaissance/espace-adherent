<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="adherent_activation_keys", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="adherent_activation_token_unique", columns="value"),
 *   @ORM\UniqueConstraint(name="adherent_activation_token_account_unique", columns={"value", "adherent_uuid"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AdherentActivationTokenRepository")
 */
final class AdherentActivationToken extends AdherentToken
{
    public function getType(): string
    {
        return 'adherent activation';
    }
}

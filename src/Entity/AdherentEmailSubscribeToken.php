<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(columns="value"),
 *     @ORM\UniqueConstraint(columns={"value", "adherent_uuid"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\AdherentEmailSubscribeTokenRepository")
 */
class AdherentEmailSubscribeToken extends AdherentToken
{
    use AuthoredTrait;
    use EntityAdministratorBlameableTrait;

    public const TRIGGER_SOURCE_ADMIN = 'admin';
    public const TRIGGER_SOURCE_PHONING = 'phoning';

    public const DURATION = '+6 months';

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $triggerSource = null;

    public function getType(): string
    {
        return 'adherent email subscribe';
    }

    public function setTriggerSource(?string $triggerSource): void
    {
        $this->triggerSource = $triggerSource;
    }
}

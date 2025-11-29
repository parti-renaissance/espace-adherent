<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @deprecated
 * @see EntityAdministratorBlameableTrait instead
 */
trait EntityAdministratorTrait
{
    /**
     * @var Administrator|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private $administrator;

    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }

    public function setAdministrator(?Administrator $administrator): void
    {
        $this->administrator = $administrator;
    }
}

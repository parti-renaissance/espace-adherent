<?php

namespace App\Scope;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

class Scope
{
    /**
     * @var string
     *
     * @SymfonySerializer\Groups({"scopes", "scope"})
     */
    private $code;

    /**
     * @var Collection|Zone[]
     *
     * @SymfonySerializer\Groups({"scopes", "scope"})
     */
    private $zones;

    /**
     * @var array
     *
     * @SymfonySerializer\Groups({"scopes"})
     */
    private $apps;

    public function __construct(string $code, array $zones, array $apps)
    {
        $this->code = $code;
        $this->zones = $zones;
        $this->apps = $apps;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return Zone[]
     */
    public function getZones(): array
    {
        return $this->zones;
    }

    public function getApps(): ?array
    {
        return $this->apps;
    }
}

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
     * @var string
     *
     * @SymfonySerializer\Groups({"scopes", "scope"})
     */
    private $name;

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

    public function __construct(string $code, string $name, array $zones, array $apps)
    {
        $this->code = $code;
        $this->name = $name;
        $this->zones = $zones;
        $this->apps = $apps;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
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

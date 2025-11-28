<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;

interface ZoneableEntityInterface
{
    /**
     * @return Collection|Zone[]
     */
    public function getZones(): Collection;

    public function addZone(Zone $zone): void;

    public function removeZone(Zone $zone): void;

    public function clearZones(): void;

    public static function getZonesPropertyName(): string;

    public static function alterQueryBuilderForZones(QueryBuilder $queryBuilder, string $rootAlias): void;
}

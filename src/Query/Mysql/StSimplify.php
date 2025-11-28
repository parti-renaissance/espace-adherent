<?php

declare(strict_types=1);

namespace App\Query\Mysql;

use LongitudeOne\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;
use LongitudeOne\Spatial\ORM\Query\AST\Functions\ReturnsGeometryInterface;

class StSimplify extends AbstractSpatialDQLFunction implements ReturnsGeometryInterface
{
    protected function getFunctionName(): string
    {
        return 'ST_Simplify';
    }

    protected function getMaxParameter(): int
    {
        return 2;
    }

    protected function getMinParameter(): int
    {
        return 2;
    }

    protected function getPlatforms(): array
    {
        return ['mysql'];
    }
}

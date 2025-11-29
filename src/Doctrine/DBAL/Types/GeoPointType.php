<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\Type;

final class GeoPointType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if ($platform instanceof MySQLPlatform) {
            return 'FLOAT (10,6)';
        }

        return $platform->getFloatDeclarationSQL($column);
    }

    public function getName(): string
    {
        return 'geo_point';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): true
    {
        return true;
    }
}

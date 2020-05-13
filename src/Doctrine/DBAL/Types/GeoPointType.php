<?php

namespace App\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;

final class GeoPointType extends Type
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        if ($platform instanceof MySqlPlatform) {
            return sprintf('FLOAT (10,6)');
        }

        return $platform->getFloatDeclarationSQL($fieldDeclaration);
    }

    public function getName()
    {
        return 'geo_point';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}

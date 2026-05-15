<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4',
            'default_table_options' => [
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
            'host' => '%env(DATABASE_HOST)%',
            'port' => '%env(DATABASE_PORT)%',
            'dbname' => '%env(DATABASE_NAME)%',
            'user' => '%env(DATABASE_USER)%',
            'password' => '%env(DATABASE_PASSWORD)%',
            'unix_socket' => '%env(DATABASE_SOCKET)%',
            'server_version' => '8.0',
            'types' => [
                'uuid' => App\Doctrine\DBAL\Types\UuidType::class,
                'phone_number' => Misd\PhoneNumberBundle\Doctrine\DBAL\Types\PhoneNumberType::class,
                'geo_point' => App\Doctrine\DBAL\Types\GeoPointType::class,
                'geometry' => LongitudeOne\Spatial\DBAL\Types\GeometryType::class,
                'polygon' => LongitudeOne\Spatial\DBAL\Types\Geometry\PolygonType::class,
                'multipolygon' => LongitudeOne\Spatial\DBAL\Types\Geometry\PolygonType::class,
            ],
        ],
        'orm' => [
            'naming_strategy' => 'doctrine.orm.naming_strategy.underscore',
            'auto_mapping' => false,
            'mappings' => [
                'App' => [
                    'is_bundle' => false,
                    'type' => 'attribute',
                    'dir' => '%kernel.project_dir%/src/Entity',
                    'prefix' => 'App\Entity',
                ],
            ],
            'dql' => [
                'datetime_functions' => [
                    'year_month' => DoctrineExtensions\Query\Mysql\YearMonth::class,
                    'year' => DoctrineExtensions\Query\Mysql\Year::class,
                    'timestampdiff' => DoctrineExtensions\Query\Mysql\TimestampDiff::class,
                    'convert_tz' => DoctrineExtensions\Query\Mysql\ConvertTz::class,
                    'now' => DoctrineExtensions\Query\Mysql\Now::class,
                    'date' => DoctrineExtensions\Query\Mysql\Date::class,
                    'date_add' => DoctrineExtensions\Query\Mysql\DateAdd::class,
                    'date_sub' => DoctrineExtensions\Query\Mysql\DateSub::class,
                ],
                'numeric_functions' => [
                    'acos' => DoctrineExtensions\Query\Mysql\Acos::class,
                    'cos' => DoctrineExtensions\Query\Mysql\Cos::class,
                    'radians' => DoctrineExtensions\Query\Mysql\Radians::class,
                    'sin' => DoctrineExtensions\Query\Mysql\Sin::class,
                    'st_geomfromtext' => LongitudeOne\Spatial\ORM\Query\AST\Functions\Standard\StGeomFromText::class,
                    'st_within' => LongitudeOne\Spatial\ORM\Query\AST\Functions\Standard\StWithin::class,
                    'st_astext' => LongitudeOne\Spatial\ORM\Query\AST\Functions\Standard\StAsText::class,
                    'st_simplify' => App\Doctrine\DBAL\StSimplify::class,
                    'point' => LongitudeOne\Spatial\ORM\Query\AST\Functions\Standard\StPoint::class,
                    'round' => DoctrineExtensions\Query\Mysql\Round::class,
                ],
                'string_functions' => [
                    'json_contains' => App\Doctrine\DBAL\JsonContains::class,
                    'find_in_set' => DoctrineExtensions\Query\Mysql\FindInSet::class,
                    'substring_index' => DoctrineExtensions\Query\Mysql\SubstringIndex::class,
                    'group_concat' => DoctrineExtensions\Query\Mysql\GroupConcat::class,
                    'concat_ws' => DoctrineExtensions\Query\Mysql\ConcatWs::class,
                    'replace' => DoctrineExtensions\Query\Mysql\Replace::class,
                    'if' => DoctrineExtensions\Query\Mysql\IfElse::class,
                    'cast' => DoctrineExtensions\Query\Mysql\Cast::class,
                ],
            ],
        ],
    ]);
};

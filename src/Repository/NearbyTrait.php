<?php

declare(strict_types=1);

namespace App\Repository;

use App\Geocoder\Coordinates;
use Doctrine\ORM\QueryBuilder;

trait NearbyTrait
{
    public function getNearbyExpression(string $alias): string
    {
        return \sprintf('(6371 * acos(cos(radians(:latitude)) * cos(radians(%1$s.postAddress.latitude))
            * cos(radians(%1$s.postAddress.longitude) - radians(:longitude)) + sin(radians(:latitude)) *
            sin(radians(%1$s.postAddress.latitude))))', $alias);
    }

    /**
     * Calculates the distance (in Km) between the subject entity and the provided geographical
     * points in a select statement. You can use this template to apply your constraints
     * by using the 'distance_between' attribute.
     *
     * Setting the hidden flag to false allow you to get an array as result containing
     * the entity and the calculated distance.
     */
    public function createNearbyQueryBuilder(Coordinates $coordinates, bool $hidden = true): QueryBuilder
    {
        return $this->updateNearByQueryBuilder($this->createQueryBuilder('n'), 'n', $coordinates, $hidden);
    }

    public function updateNearByQueryBuilder(QueryBuilder $queryBuilder, string $alias, Coordinates $coordinates, bool $hidden = true): QueryBuilder
    {
        $hidden = $hidden ? 'hidden' : '';

        return $queryBuilder
            ->addSelect($this->getNearbyExpression($alias).' as '.$hidden.' distance_between')
            ->setParameter('latitude', $coordinates->getLatitude())
            ->setParameter('longitude', $coordinates->getLongitude())
            ->where($alias.'.postAddress.latitude IS NOT NULL')
            ->andWhere($alias.'.postAddress.longitude IS NOT NULL')
            ->addOrderBy('distance_between', 'ASC')
        ;
    }
}

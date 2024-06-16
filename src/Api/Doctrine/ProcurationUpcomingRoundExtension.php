<?php

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\ProcurationV2\AbstractProcuration;
use App\Entity\ProcurationV2\Proxy;
use Doctrine\ORM\QueryBuilder;

class ProcurationUpcomingRoundExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (!is_a($resourceClass, AbstractProcuration::class, true)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->innerJoin(
                sprintf(
                    '%s.%s',
                    $rootAlias,
                    is_a($resourceClass, Proxy::class, true)
                        ? 'proxySlots'
                        : 'requestSlots'
                ),
                'slot'
            )
            ->innerJoin('slot.round', 'round')
            ->addSelect('round.date AS HIDDEN round_date')
            ->andWhere('round.date > NOW()')
            ->orderBy("$rootAlias.createdAt", 'ASC')
            ->addOrderBy('round_date', 'ASC')
        ;
    }
}
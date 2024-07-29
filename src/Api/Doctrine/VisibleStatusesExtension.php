<?php

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\VisibleStatusesInterface;
use Doctrine\ORM\QueryBuilder;

class VisibleStatusesExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->modifyQuery($queryBuilder, $resourceClass);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (is_a($resourceClass, VisibleStatusesInterface::class, true)) {
            $queryBuilder
                ->andWhere(\sprintf('%s.status IN (:statuses)', $queryBuilder->getRootAliases()[0]))
                ->setParameter('statuses', $resourceClass::getVisibleStatuses())
            ;
        }
    }
}

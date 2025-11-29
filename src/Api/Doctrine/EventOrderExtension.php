<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Event\Event;
use Doctrine\ORM\QueryBuilder;

class EventOrderExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, Event::class, true)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        /* Apply additional sorting by unique column (ID) to avoid issue with duplicated
         * rows between two pages when ordering by a non-unique column (ex.: date). */
        if ($queryBuilder->getDQLPart('orderBy')) {
            $queryBuilder->addOrderBy($alias.'.id', 'DESC');
        }
    }
}

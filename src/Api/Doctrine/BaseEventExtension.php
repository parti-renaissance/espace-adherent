<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Event\EventTypeEnum;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class BaseEventExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ) {
        if (!is_a($resourceClass, BaseEvent::class, true)) {
            return;
        }

        $this->modifyQuery($queryBuilder, BaseEvent::STATUSES, $operationName);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (!is_a($resourceClass, BaseEvent::class, true)) {
            return;
        }

        $this->modifyQuery($queryBuilder, BaseEvent::ACTIVE_STATUSES, $operationName);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, array $statuses, string $operationName = null): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        if (\in_array($operationName, ['get_public', 'get'])
            && !$this->security->getUser() instanceof Adherent) {
            $queryBuilder->andWhere("$alias.private = false");
        }

        $queryBuilder
            ->andWhere("$alias.published = :true")
            ->andWhere("$alias.status IN (:statuses)")
            ->andWhere("$alias NOT INSTANCE OF :institutional")
            ->setParameter('true', true)
            ->setParameter('statuses', $statuses)
            ->setParameter('institutional', EventTypeEnum::TYPE_INSTITUTIONAL)
        ;
    }
}

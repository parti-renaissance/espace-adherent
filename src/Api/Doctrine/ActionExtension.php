<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Action\Action;
use App\Geocoder\Coordinates;
use App\Repository\Action\ActionRepository;
use Doctrine\ORM\QueryBuilder;

class ActionExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private readonly ActionRepository $actionRepository)
    {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, Action::class, true)) {
            return;
        }

        if (PrivatePublicContextBuilder::CONTEXT_PRIVATE === $context[PrivatePublicContextBuilder::CONTEXT_KEY]) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->addSelect('participant')
            ->leftJoin("$alias.participants", 'participant')
        ;

        $filters = $context['filters'] ?? [];

        $latitude = (float) ($filters['latitude'] ?? null);
        $longitude = (float) ($filters['longitude'] ?? null);

        $queryBuilder
            ->addSelect("CASE WHEN $alias.date >= NOW() THEN 0 ELSE 1 END AS HIDDEN is_future")
            ->addOrderBy('is_future', 'ASC')
            ->addOrderBy($alias.'.date', 'DESC')
        ;

        if ($latitude && $longitude) {
            $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
                ->from(Action::class, 'a2')
                ->select('DISTINCT a2.id')
            ;

            $this->actionRepository
                ->updateNearByQueryBuilder($subQuery, 'a2', new Coordinates($latitude, $longitude))
                ->setMaxResults(300)
            ;

            $ids = $subQuery->getQuery()->getResult();

            $queryBuilder
                ->andWhere("$alias.id IN (:ids)")
                ->setParameter('ids', $ids)
            ;

            return;
        }

        $queryBuilder
            ->andWhere("$alias.date >= :date")
            ->andWhere("$alias.status = :status")
            ->setParameter('date', new \DateTime('-1 hour'))
            ->setParameter('status', Action::STATUS_SCHEDULED)
        ;
    }
}

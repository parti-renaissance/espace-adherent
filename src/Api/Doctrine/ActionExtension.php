<?php

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

        if ($latitude && $longitude) {
            $this->actionRepository->updateNearByQueryBuilder($queryBuilder, $alias, new Coordinates($latitude, $longitude));

            return;
        }

        $queryBuilder
            ->andWhere("$alias.date >= :date")
            ->andWhere("$alias.status = :status")
            ->setParameter('date', new \DateTime('-1 hour'))
            ->setParameter('status', Action::STATUS_SCHEDULED)
            ->addOrderBy("$alias.date", 'ASC')
        ;
    }
}

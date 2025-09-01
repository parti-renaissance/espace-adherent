<?php

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\InscriptionStatusEnum;
use Doctrine\ORM\QueryBuilder;

class NationalEventInscriptionExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, EventInscription::class, true)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere("$alias.status NOT IN (:forbidden_status)")
            ->setParameter('forbidden_status', [
                InscriptionStatusEnum::DUPLICATE,
                InscriptionStatusEnum::REFUSED,
            ])
        ;
    }
}

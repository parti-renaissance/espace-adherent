<?php

namespace AppBundle\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use AppBundle\Entity\IdeasWorkshop\Consultation;
use Doctrine\ORM\QueryBuilder;

class ConsultationExtension implements ContextAwareQueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ) {
        if (Consultation::class === $resourceClass) {
            $queryBuilder
                ->andWhere(sprintf('%s.endedAt >= :now', $queryBuilder->getRootAliases()[0]))
                ->setParameter('now', new \DateTime())
            ;
        }
    }
}

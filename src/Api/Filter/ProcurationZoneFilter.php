<?php

declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Procuration\AbstractProcuration;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Symfony\Contracts\Service\Attribute\Required;

class ProcurationZoneFilter extends AbstractFilter
{
    private ZoneRepository $zoneRepository;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if ('zone' !== $property || empty($value) || !Uuid::isValid($value)) {
            return;
        }

        if (!is_a($resourceClass, AbstractProcuration::class, true)) {
            return;
        }

        if (!$zone = $this->zoneRepository->findOneByUuid($value)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere("FIND_IN_SET(:zone_id, $alias.zoneIds) > 0")
            ->setParameter('zone_id', $zone->getId())
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return ['zone' => [
            'property' => 'zone',
            'type' => 'string',
            'required' => false,
            'swagger' => [
                'description' => 'Filter by zone (uuid)',
                'name' => 'zone',
                'type' => 'string',
            ],
        ]];
    }

    #[Required]
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}

<?php

declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Jecoute\News;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Service\Attribute\Required;

final class JecouteNewsZipCodeFilter extends AbstractFilter
{
    private ?ZoneRepository $zoneRepository = null;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (News::class !== $resourceClass
            || 'zipCode' !== $property
            || empty($value)
        ) {
            return;
        }

        if (!$zones = $this->zoneRepository->findByPostalCode($value)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->leftJoin(\sprintf('%s.zone', $rootAlias), 'zone')
            ->leftJoin('zone.children', 'children')
            ->andWhere('zone IS NULL OR zone IN (:zones) OR children IN (:zones)')
            ->setParameter('zones', $zones)
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description['zipCode'] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter by zipCode.',
                    'name' => 'zipCode',
                    'type' => 'string',
                ],
            ];
        }

        return $description;
    }

    #[Required]
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}

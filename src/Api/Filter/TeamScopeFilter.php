<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Adherent;
use App\Entity\Team\Team;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;
use App\Team\TeamVisibilityEnum;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

final class TeamScopeFilter extends AbstractContextAwareFilter
{
    private const OPERATION_NAMES = ['get'];

    private ?Security $security = null;
    private ?ScopeGeneratorResolver $scopeGeneratorResolver = null;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        $user = $this->security->getUser();

        if (!$user instanceof Adherent) {
            return;
        }

        $scopeGenerator = $this->scopeGeneratorResolver->resolve();

        if (
            !is_a($resourceClass, Team::class, true)
            || !\in_array($operationName, self::OPERATION_NAMES, true)
            || null === $scopeGenerator
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        if (\in_array($scopeGenerator->getCode(), ScopeEnum::NATIONAL_SCOPES, true)) {
            $queryBuilder
                ->andWhere("$alias.visibility = :visibility")
                ->setParameter('visibility', TeamVisibilityEnum::NATIONAL)
            ;

            return;
        }

        $author = $scopeGenerator->isDelegatedAccess()
            ? $scopeGenerator->getDelegatedAccess()->getDelegator()
            : $user
        ;

        $zones = $scopeGenerator->getZones($author);

        $queryBuilder
            ->andWhere("$alias.visibility = :visibility")
            ->setParameter('visibility', TeamVisibilityEnum::LOCAL)
            ->innerJoin("$alias.zone", 'zone')
            ->leftJoin('zone.parents', 'parent_zone')
            ->andWhere('zone IN (:zones) OR parent_zone IN (:zones)')
            ->setParameter('zones', $zones)
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return [];
    }

    /**
     * @required
     */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    /**
     * @required
     */
    public function setScopeGeneratorResolver(ScopeGeneratorResolver $scopeGeneratorResolver): void
    {
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }
}

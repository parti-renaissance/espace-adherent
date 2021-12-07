<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Adherent;
use App\Entity\Team\Team;
use App\Scope\GeneralScopeGenerator;
use App\Scope\ScopeEnum;
use App\Team\TeamVisibilityEnum;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

final class TeamScopeFilter extends AbstractContextAwareFilter
{
    private const PROPERTY_NAME = 'scope';
    private const OPERATION_NAMES = ['get'];

    private ?GeneralScopeGenerator $generalScopeGenerator = null;
    private ?Security $security = null;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        $user = $this->security->getUser();

        if (
            (!$user instanceof Adherent)
            || !is_a($resourceClass, Team::class, true)
            || self::PROPERTY_NAME !== $property
            || !\in_array($operationName, self::OPERATION_NAMES, true)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $scopeGenerator = $this->generalScopeGenerator->getGenerator($value, $user);

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
        return [
            self::PROPERTY_NAME => [
                'property' => null,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }

    /**
     * @required
     */
    public function setGeneralScopeGenerator(GeneralScopeGenerator $generalScopeGenerator): void
    {
        $this->generalScopeGenerator = $generalScopeGenerator;
    }

    /**
     * @required
     */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }
}

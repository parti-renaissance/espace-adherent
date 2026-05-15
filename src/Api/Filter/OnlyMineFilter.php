<?php

declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\AuthoredInterface;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\Attribute\Required;

final class OnlyMineFilter extends AbstractFilter
{
    public const string PROPERTY_NAME = 'only_mine';

    private Security $security;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (
            !is_a($resourceClass, AuthoredInterface::class, true)
            || self::PROPERTY_NAME !== $property
            || !$user = $this->security->getUser()
        ) {
            return;
        }

        $scope = $this->scopeGeneratorResolver->generate();
        $user = $scope && $scope->getDelegatedAccess() ? $scope->getDelegator() : $user;

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(\sprintf('%s.author = :only_mine_author', $alias))
            ->setParameter('only_mine_author', $user)
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        if (!is_a($resourceClass, AuthoredInterface::class, true)) {
            return [];
        }

        return [
            self::PROPERTY_NAME => [
                'property' => null,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }

    #[Required]
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    #[Required]
    public function setScopeGeneratorResolver(ScopeGeneratorResolver $scopeGeneratorResolver): void
    {
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }
}

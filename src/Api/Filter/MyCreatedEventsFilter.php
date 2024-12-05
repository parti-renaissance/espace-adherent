<?php

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Event\Event;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\Attribute\Required;

final class MyCreatedEventsFilter extends AbstractFilter
{
    private const PROPERTY_NAME = 'only_mine';

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
            Event::class !== $resourceClass
            || self::PROPERTY_NAME !== $property
            || !$user = $this->security->getUser()
        ) {
            return;
        }

        $scope = $this->scopeGeneratorResolver->generate();
        $user = $scope && $scope->getDelegatedAccess() ? $scope->getDelegator() : $user;

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(\sprintf('%s.author = :organizer', $alias))
            ->setParameter('organizer', $user)
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

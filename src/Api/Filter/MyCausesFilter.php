<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Coalition\Cause;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

final class MyCausesFilter extends AbstractContextAwareFilter
{
    private const PROPERTY_NAME = 'onlyMine';

    /** @var Security */
    private $security;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (
            Cause::class !== $resourceClass
            || self::PROPERTY_NAME !== $property
            || !$user = $this->security->getUser()
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->addSelect(sprintf('IF(%s.author = :adherent, 1, 0) as HIDDEN created', $alias))
            ->leftJoin($alias.'.followers', 'follower')
            ->andWhere(sprintf('(%s.author = :adherent OR follower.adherent = :adherent)', $alias))
            ->setParameter('adherent', $user)
            ->orderBy('created', 'DESC')
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

    /** @required */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }
}

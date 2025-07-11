<?php

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Email\EmailTemplate;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class EmailTemplateExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly Security $security,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (EmailTemplate::class !== $resourceClass) {
            return;
        }

        $scope = $this->scopeGeneratorResolver->generate();
        $user = $scope && $scope->getDelegatedAccess() ? $scope->getDelegator() : $this->security->getUser();

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->leftJoin("$rootAlias.zones", 'zone')
            ->andWhere((new Orx())
                ->add($queryBuilder->expr()->andX()
                    ->add(\sprintf('%1$s.scopes IS NOT NULL AND FIND_IN_SET(:scope, %1$s.scopes) > 0', $rootAlias))
                    ->add($queryBuilder->expr()->orX()
                            ->add("$rootAlias.zones IS EMPTY")
                            ->add('zone IN (:zones)')
                    )
                )
                ->add(\sprintf('%s.createdByAdherent = :adherent', $rootAlias))
            )
            ->andWhere($rootAlias.'.isStatutory = :statutory_value')
            ->setParameter('statutory_value', isset($context['filters']['is_statutory']) && '1' === $context['filters']['is_statutory'])
            ->setParameter('adherent', $user)
            ->setParameter('scope', $scope->getMainCode())
            ->setParameter('zones', $scope->getZones())
        ;
    }
}

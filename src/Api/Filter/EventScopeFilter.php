<?php

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Repository\Event\BaseEventRepository;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class EventScopeFilter extends AbstractScopeFilter
{
    private BaseEventRepository $baseEventRepository;
    private AuthorizationCheckerInterface $authorizationChecker;

    protected function needApplyFilter(string $property, string $resourceClass, string $operationName = null): bool
    {
        return is_a($resourceClass, BaseEvent::class, true)
            && $this->authorizationChecker->isGranted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN');
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];

        $this
            ->baseEventRepository
            ->withGeoZones(
                $scopeGenerator->generate($currentUser)->getZones(),
                $queryBuilder,
                $alias,
                BaseEvent::class,
                'e2',
                'zones',
                'z2'
            )
        ;
    }

    /**
     * @required
     */
    public function setBaseEventRepository(BaseEventRepository $baseEventRepository): void
    {
        $this->baseEventRepository = $baseEventRepository;
    }

    /**
     * @required
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }
}

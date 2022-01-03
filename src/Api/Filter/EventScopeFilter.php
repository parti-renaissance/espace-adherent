<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Event\BaseEvent;
use App\Repository\Event\BaseEventRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class EventScopeFilter extends AbstractScopeFilter
{
    private BaseEventRepository $baseEventRepository;
    private AuthorizationCheckerInterface $authorizationChecker;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (
            !is_a($resourceClass, BaseEvent::class, true)
            || !$this->authorizationChecker->isGranted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN')
            || !$this->needApplyFilter($property, $operationName)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $this
            ->baseEventRepository
            ->withGeoZones(
                $this->getScopeGenerator($value)->getZones($this->getUser($value)),
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

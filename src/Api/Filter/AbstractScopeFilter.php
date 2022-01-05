<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Adherent;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

abstract class AbstractScopeFilter extends AbstractContextAwareFilter
{
    private const PROPERTY_NAME = 'scope';

    protected GeneralScopeGenerator $generalScopeGenerator;
    protected Security $security;

    final protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (self::PROPERTY_NAME !== $property || !\in_array($operationName, $this->getAllowedOperationNames(), true)) {
            return;
        }

        if (!$this->needApplyFilter($property, $resourceClass, $operationName)) {
            return;
        }

        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof Adherent) {
            return;
        }

        try {
            $scopeGenerator = $this->generalScopeGenerator->getGenerator($value, $currentUser);
        } catch (ScopeExceptionInterface $e) {
            return;
        }

        $this->applyFilter($queryBuilder, $currentUser, $scopeGenerator);
    }

    abstract protected function needApplyFilter(
        string $property,
        string $resourceClass,
        string $operationName = null
    ): bool;

    abstract protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator
    ): void;

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

    protected function getAllowedOperationNames(): array
    {
        return ['get'];
    }
}

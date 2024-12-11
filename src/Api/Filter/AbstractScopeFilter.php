<?php

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Adherent;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractScopeFilter extends AbstractFilter
{
    private const PROPERTY_NAME = 'scope';

    protected GeneralScopeGenerator $generalScopeGenerator;
    protected Security $security;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (self::PROPERTY_NAME !== $property || !$this->isValidOperation($operation)) {
            return;
        }

        if (!$this->needApplyFilter($property, $resourceClass)) {
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

        $this->applyFilter($queryBuilder, $currentUser, $scopeGenerator, $resourceClass, $context);
    }

    abstract protected function needApplyFilter(string $property, string $resourceClass): bool;

    abstract protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator,
        string $resourceClass,
        array $context,
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

    #[Required]
    public function setGeneralScopeGenerator(GeneralScopeGenerator $generalScopeGenerator): void
    {
        $this->generalScopeGenerator = $generalScopeGenerator;
    }

    #[Required]
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    protected function getAllowedOperationNames(string $resourceClass): array
    {
        return ['{uuid}_get', '{uuid}{._format}_get', '_get_collection'];
    }

    private function isValidOperation(?Operation $operation): bool
    {
        if (!$operation) {
            return false;
        }

        $operationName = $operation->getName();

        foreach ($this->getAllowedOperationNames($operation->getClass()) as $routeSuffix) {
            if (str_ends_with($operationName, $routeSuffix)) {
                return true;
            }
        }

        return false;
    }
}

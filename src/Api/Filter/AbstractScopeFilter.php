<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use App\Entity\Adherent;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Generator\ScopeGeneratorInterface;
use Symfony\Component\Security\Core\Security;

abstract class AbstractScopeFilter extends AbstractContextAwareFilter
{
    protected const PROPERTY_NAME = 'scope';
    protected const OPERATION_NAMES = ['get'];

    protected GeneralScopeGenerator $generalScopeGenerator;
    protected ScopeGeneratorInterface $scopeGenerator;
    protected Security $security;
    protected ?Adherent $user = null;

    protected function needApplyFilter(string $property, string $operationName = null): bool
    {
        $this->user = $this->security->getUser();

        if (
            static::PROPERTY_NAME !== $property
            || !\in_array($operationName, static::OPERATION_NAMES, true)
        ) {
            return false;
        }

        return true;
    }

    protected function getUser(string $value): ?Adherent
    {
        $this->scopeGenerator = $this->getScopeGenerator($value);

        return $this->scopeGenerator->isDelegatedAccess()
            ? $this->scopeGenerator->getDelegatedAccess()->getDelegator()
            : $this->user
        ;
    }

    protected function getScopeGenerator($value): ScopeGeneratorInterface
    {
        return $this->scopeGenerator ?? ($this->scopeGenerator = $this->generalScopeGenerator->getGenerator($value, $this->user));
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            static::PROPERTY_NAME => [
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

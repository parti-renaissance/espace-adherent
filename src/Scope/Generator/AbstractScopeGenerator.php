<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\Scope as ScopeEntity;
use App\Repository\ScopeRepository;
use App\Scope\DelegatedAccess as ScopeDelegatedAccess;
use App\Scope\FeatureEnum;
use App\Scope\Scope;

abstract class AbstractScopeGenerator implements ScopeGeneratorInterface
{
    private $scopeRepository;

    /** @var DelegatedAccess|null */
    private $delegatedAccess;

    public function __construct(ScopeRepository $scopeRepository)
    {
        $this->scopeRepository = $scopeRepository;
    }

    final public function generate(Adherent $adherent): Scope
    {
        $scopeEntity = $this->findScope($this->getCode());

        $delegatedAccess = null;
        if ($this->delegatedAccess) {
            $delegatedAccess = new ScopeDelegatedAccess(
                $this->delegatedAccess->getDelegator(),
                $this->delegatedAccess->getType(),
                $this->delegatedAccess->getRole()
            );
        }

        $scope = new Scope(
            $this->getScopeCode($scopeEntity),
            $this->getScopeName($scopeEntity),
            $this->getZones($this->delegatedAccess ? $this->delegatedAccess->getDelegator() : $adherent),
            $scopeEntity->getApps(),
            $this->getFeatures($scopeEntity, $adherent),
            $adherent,
            $delegatedAccess
        );

        $this->delegatedAccess = null;

        return $this->enrichAttributes($scope, $adherent);
    }

    public function setDelegatedAccess(DelegatedAccess $delegatedAccess): void
    {
        $this->delegatedAccess = $delegatedAccess;
    }

    public function getDelegatedAccess(): ?DelegatedAccess
    {
        return $this->delegatedAccess;
    }

    public function isDelegatedAccess(): bool
    {
        return (bool) $this->delegatedAccess;
    }

    protected function getZones(Adherent $adherent): array
    {
        $role = $adherent->findZoneBasedRole($this->getCode());

        return $role ? $role->getZones()->toArray() : [];
    }

    private function findScope(string $code): ?ScopeEntity
    {
        $scope = $this->scopeRepository->findOneByCode($code);

        if (!$scope) {
            throw new \InvalidArgumentException(\sprintf('Could not find any Scope with code "%s" in database.', $code));
        }

        return $scope;
    }

    private function getScopeCode(ScopeEntity $scopeEntity): string
    {
        return $this->delegatedAccess
            ? \sprintf('%s%s', self::DELEGATED_SCOPE_PREFIX, $this->delegatedAccess->getUuid()->toString())
            : $scopeEntity->getCode();
    }

    private function getScopeName(ScopeEntity $scopeEntity): string
    {
        $name = $scopeEntity->getName();

        if ($this->delegatedAccess) {
            $name .= ' délégué';
        }

        return $name;
    }

    private function getFeatures(ScopeEntity $scopeEntity, Adherent $adherent): array
    {
        $scopeFeatures = $scopeEntity->getFeatures();

        if ($scopeEntity->canaryFeatures && !$adherent->canaryTester) {
            $scopeFeatures = array_values(array_diff($scopeFeatures, $scopeEntity->canaryFeatures));
        }

        if ($this->delegatedAccess) {
            if ($delegatedScopeFeatures = $this->delegatedAccess->getScopeFeatures()) {
                return array_values(array_intersect($scopeFeatures, array_merge(FeatureEnum::DELEGATED_ACCESSES_BY_DEFAULT, $delegatedScopeFeatures)));
            }

            return [];
        }

        return $scopeFeatures;
    }

    protected function enrichAttributes(Scope $scope, Adherent $adherent): Scope
    {
        return $scope;
    }
}

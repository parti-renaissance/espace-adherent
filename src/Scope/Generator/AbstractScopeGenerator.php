<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\Scope as ScopeEntity;
use App\Repository\ScopeRepository;
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

        $scope = new Scope(
            $this->getScopeCode($scopeEntity),
            $this->getScopeName($scopeEntity),
            $this->getZones($this->delegatedAccess ? $this->delegatedAccess->getDelegator() : $adherent),
            $scopeEntity->getApps(),
            $this->getFeatures($scopeEntity)
        );

        $this->delegatedAccess = null;

        return $scope;
    }

    public function setDelegatedAccess(DelegatedAccess $delegatedAccess): void
    {
        $this->delegatedAccess = $delegatedAccess;
    }

    abstract protected function getZones(Adherent $adherent): array;

    private function findScope(string $code): ?ScopeEntity
    {
        $scope = $this->scopeRepository->findOneByCode($code);

        if (!$scope) {
            throw new \InvalidArgumentException(sprintf('Could not find any Scope with code "%s" in database.', $code));
        }

        return $scope;
    }

    private function getScopeCode(ScopeEntity $scopeEntity): string
    {
        return $this->delegatedAccess
            ? sprintf('%s%s', self::DELEGATED_SCOPE_PREFIX, $this->delegatedAccess->getUuid()->toString())
            : $scopeEntity->getCode()
        ;
    }

    private function getScopeName(ScopeEntity $scopeEntity): string
    {
        $name = $scopeEntity->getName();

        if ($this->delegatedAccess) {
            $name .= ' délégué';
        }

        return $name;
    }

    private function getFeatures(ScopeEntity $scopeEntity): array
    {
        $scopeFeatures = $scopeEntity->getFeatures();

        if ($this->delegatedAccess) {
            $inheritedFeatures = [
                FeatureEnum::DASHBOARD,
            ];

            foreach ($this->delegatedAccess->getAccesses() as $delegatedFeature) {
                if (\array_key_exists($delegatedFeature, FeatureEnum::DELEGATED_ACCESSES_MAPPING)) {
                    $inheritedFeatures[] = FeatureEnum::DELEGATED_ACCESSES_MAPPING[$delegatedFeature];
                }
            }

            return array_values(array_intersect($scopeFeatures, $inheritedFeatures));
        }

        return $scopeFeatures;
    }
}

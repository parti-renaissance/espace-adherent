<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\Scope as ScopeEntity;
use App\Repository\ScopeRepository;
use App\Scope\DelegatedAccess as ScopeDelegatedAccess;
use App\Scope\FeatureEnum;
use App\Scope\Scope;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractScopeGenerator implements ScopeGeneratorInterface
{
    /** @var DelegatedAccess|null */
    private $delegatedAccess;

    public function __construct(
        private readonly ScopeRepository $scopeRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasZoneBasedRole($this->getCode());
    }

    final public function generate(Adherent $adherent): Scope
    {
        $scopeEntity = $this->findScope($this->getCode());

        $delegatedAccess = null;
        if ($this->delegatedAccess) {
            $delegatedAccess = new ScopeDelegatedAccess(
                $this->delegatedAccess->getDelegator(),
                $this->delegatedAccess->getType(),
                $this->translateDelegatedRole($this->delegatedAccess->roleCode, $adherent->getGender(), $this->delegatedAccess->getRole())
            );
        }

        $scope = new Scope(
            $this->getScopeCode($scopeEntity),
            $this->getScopeName($scopeEntity, $adherent),
            $this->getScopeRoleName($scopeEntity, $adherent),
            $this->getZones($this->delegatedAccess ? $this->delegatedAccess->getDelegator() : $adherent),
            $scopeEntity->getApps(),
            $this->getFeatures($scopeEntity, $adherent),
            $adherent,
            $delegatedAccess,
            $this->getScopeMainRoleName($scopeEntity, $adherent),
        );

        $this->delegatedAccess = null;

        $scope->addAttribute('theme', $scopeEntity->getTheme());

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

    private function findScope(string $code): ScopeEntity
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

    private function getScopeName(ScopeEntity $scopeEntity, Adherent $currentUser): string
    {
        $name = $this->translator->trans($key = 'role.'.$scopeEntity->getCode(), ['gender' => $currentUser->getGender()]);

        if ($name === $key) {
            $name = $scopeEntity->getName();
        }

        if ($this->delegatedAccess) {
            $name .= ' délégué'.($currentUser->isFemale() ? 'e' : '');
        }

        return $name;
    }

    private function getScopeRoleName(ScopeEntity $scopeEntity, Adherent $currentUser): string
    {
        if ($this->delegatedAccess) {
            return $this->translateDelegatedRole($this->delegatedAccess->roleCode, $currentUser->getGender(), $this->delegatedAccess->getRole());
        }

        $value = $this->translator->trans($key = 'scope.role.'.$scopeEntity->getCode(), ['gender' => $currentUser->getGender()]);

        if ($value !== $key) {
            return $value;
        }

        return $scopeEntity->getName();
    }

    private function getScopeMainRoleName(ScopeEntity $scopeEntity, Adherent $currentUser): ?string
    {
        if (!$this->delegatedAccess) {
            return null;
        }

        $value = $this->translator->trans($key = 'scope.role.'.$scopeEntity->getCode(), ['gender' => $this->delegatedAccess->getDelegator()->getGender()]);

        if ($value !== $key) {
            return $value;
        }

        return $scopeEntity->getName();
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

    private function translateDelegatedRole(?string $roleCode, ?string $gender, string $fallback): string
    {
        if (!$roleCode) {
            return $fallback;
        }

        $key = 'my_team_member.role.'.$roleCode;
        $role = $this->translator->trans($key, ['gender' => $gender]);

        if ($role === $key) {
            $role = $fallback;
        }

        return $role;
    }
}

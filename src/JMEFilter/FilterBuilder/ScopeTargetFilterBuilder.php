<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\Entity\Scope;
use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\Types\DefinedTypes\ScopeTarget;
use App\MyTeam\RoleEnum;
use App\Repository\ScopeRepository;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScopeTargetFilterBuilder implements FilterBuilderInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ScopeRepository $scopeRepository,
    ) {
    }

    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        return new FilterCollectionBuilder()
            ->createFrom(ScopeTarget::class)
            ->setInstances($this->buildInstances())
            ->setAllowCustomRole(true)
            ->getFilters()
        ;
    }

    private function buildInstances(): array
    {
        $instances = [];
        $scopes = $this->loadScopes();

        foreach (ScopeEnum::SCOPE_TARGET_CHOICES as $code) {
            $scopeEntity = $scopes[$code] ?? null;
            $features = $scopeEntity?->getFeatures() ?? [];
            $hasMyTeam = \in_array(FeatureEnum::MY_TEAM, $features, true);
            $hasCustomRole = \in_array(FeatureEnum::MY_TEAM_CUSTOM_ROLE, $features, true);

            $instances[] = [
                'name' => ScopeEnum::SCOPE_INSTANCES[$code] ?? $scopeEntity?->getName(),
                'code' => $code,
                'main_role' => $this->getScopeLabel($code),
                'team_roles' => $hasMyTeam ? $this->buildTeamRoleChoices($hasCustomRole) : [],
            ];
        }

        return $instances;
    }

    /**
     * @return Scope[]
     */
    private function loadScopes(): array
    {
        $indexed = [];

        foreach ($this->scopeRepository->findBy(['code' => ScopeEnum::SCOPE_TARGET_CHOICES]) as $scope) {
            $indexed[$scope->getCode()] = $scope;
        }

        return $indexed;
    }

    private function getScopeLabel(string $code): string
    {
        $key = 'scope.role.'.$code;
        $label = $this->translator->trans($key, ['gender' => 'male']);

        if ($label === $key) {
            return ucfirst(str_replace('_', ' ', $code));
        }

        return $label;
    }

    private function buildTeamRoleChoices(bool $includeCustomRole): array
    {
        $choices = [];

        foreach (RoleEnum::LABELS as $code => $label) {
            $choices[] = [
                'code' => $code,
                'label' => $label,
            ];
        }

        if ($includeCustomRole) {
            $choices[] = [
                'code' => RoleEnum::CUSTOM_ROLE,
                'label' => 'Rôle personnalisé',
            ];
        }

        return $choices;
    }
}

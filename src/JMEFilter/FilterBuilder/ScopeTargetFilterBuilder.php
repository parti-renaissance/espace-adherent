<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\ScopeTargetFilterGroup;
use App\JMEFilter\Types\DefinedTypes\ScopeTarget;
use App\MyTeam\RoleEnum;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScopeTargetFilterBuilder implements FilterBuilderInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function supports(string $scope, ?string $feature = null): bool
    {
        return \in_array($scope, ScopeEnum::NATIONAL_SCOPES, true) && FeatureEnum::MESSAGES === $feature;
    }

    public function build(string $scope, ?string $feature = null): array
    {
        return new FilterCollectionBuilder()
            ->createFrom(ScopeTarget::class)
            ->setScopes($this->buildScopeChoices())
            ->setTeamRoles($this->buildTeamRoleChoices())
            ->setAllowCustomRole(true)
            ->getFilters()
        ;
    }

    public function getGroup(string $scope, ?string $feature = null): string
    {
        return ScopeTargetFilterGroup::class;
    }

    private function buildScopeChoices(): array
    {
        $choices = [];

        foreach (ScopeEnum::ALL as $code) {
            $choices[] = [
                'code' => $code,
                'label' => $this->getScopeLabel($code),
            ];
        }

        return $choices;
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

    private function buildTeamRoleChoices(): array
    {
        $choices = [];

        foreach (RoleEnum::LABELS as $code => $label) {
            $choices[] = [
                'code' => $code,
                'label' => $label,
            ];
        }

        return $choices;
    }
}

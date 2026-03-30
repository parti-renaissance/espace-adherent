<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\Types\DefinedTypes\ScopeTarget;
use App\MyTeam\RoleEnum;
use App\Scope\ScopeEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScopeTargetFilterBuilder implements FilterBuilderInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
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
        $teamRoles = $this->buildTeamRoleChoices();

        foreach (ScopeEnum::SCOPE_TARGET_CHOICES as $code) {
            $instances[] = [
                'name' => ScopeEnum::SCOPE_INSTANCES[$code] ?? null,
                'code' => $code,
                'main_role' => $this->getScopeLabel($code),
                'team_roles' => $teamRoles,
            ];
        }

        return $instances;
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

        $choices[] = [
            'code' => RoleEnum::CUSTOM_ROLE,
            'label' => 'Rôle personnalisé',
        ];

        return $choices;
    }
}

<?php

declare(strict_types=1);

namespace App\JMEFilter\Types\DefinedTypes;

use App\JMEFilter\FilterTypeEnum;
use App\JMEFilter\Types\AbstractFilter;

class ScopeTarget extends AbstractFilter
{
    public function __construct(array $options = [])
    {
        parent::__construct(
            $options['code'] ?? 'scope_targets',
            $options['label'] ?? 'Cadres & Équipes'
        );
    }

    public function setScopes(array $scopes): void
    {
        $this->addOption('scopes', $scopes);
    }

    public function setTeamRoles(array $roles): void
    {
        $this->addOption('team_roles', $roles);
    }

    public function setAllowCustomRole(bool $value): void
    {
        $this->addOption('allow_custom_role', $value);
    }

    protected function _getType(): string
    {
        return FilterTypeEnum::SCOPE_TARGET;
    }
}

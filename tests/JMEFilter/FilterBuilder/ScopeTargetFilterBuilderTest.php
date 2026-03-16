<?php

declare(strict_types=1);

namespace Tests\App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterBuilder\ScopeTargetFilterBuilder;
use App\JMEFilter\FilterGroup\ScopeTargetFilterGroup;
use App\JMEFilter\Types\DefinedTypes\ScopeTarget;
use App\MyTeam\RoleEnum;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScopeTargetFilterBuilderTest extends TestCase
{
    private ScopeTargetFilterBuilder $builder;

    protected function setUp(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturnCallback(function (string $key, array $params) {
                $translations = [
                    'scope.role.deputy' => 'Député',
                    'scope.role.national' => 'Rôle national',
                ];

                return $translations[$key] ?? $key;
            })
        ;

        $this->builder = new ScopeTargetFilterBuilder($translator);
    }

    public function testSupportsReturnsTrueForNationalScopeWithMessagesFeature(): void
    {
        $this->assertTrue($this->builder->supports(ScopeEnum::NATIONAL, FeatureEnum::MESSAGES));
        $this->assertTrue($this->builder->supports(ScopeEnum::NATIONAL_COMMUNICATION, FeatureEnum::MESSAGES));
        $this->assertTrue($this->builder->supports(ScopeEnum::NATIONAL_TERRITORIES_DIVISION, FeatureEnum::MESSAGES));
    }

    public function testSupportsReturnsFalseForNonNationalScope(): void
    {
        $this->assertFalse($this->builder->supports(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY, FeatureEnum::MESSAGES));
        $this->assertFalse($this->builder->supports(ScopeEnum::REGIONAL_COORDINATOR, FeatureEnum::MESSAGES));
        $this->assertFalse($this->builder->supports(ScopeEnum::ANIMATOR, FeatureEnum::MESSAGES));
    }

    public function testSupportsReturnsFalseForWrongFeature(): void
    {
        $this->assertFalse($this->builder->supports(ScopeEnum::NATIONAL, FeatureEnum::PUBLICATIONS));
        $this->assertFalse($this->builder->supports(ScopeEnum::NATIONAL, null));
    }

    public function testBuildReturnsArrayWithScopeTargetFilter(): void
    {
        $filters = $this->builder->build(ScopeEnum::NATIONAL);

        $this->assertCount(1, $filters);
        $this->assertInstanceOf(ScopeTarget::class, $filters[0]);
    }

    public function testBuildContainsScopesOption(): void
    {
        $filters = $this->builder->build(ScopeEnum::NATIONAL);
        $filter = $filters[0];
        $options = $filter->getOptions();

        $this->assertArrayHasKey('scopes', $options);
        $this->assertIsArray($options['scopes']);
        $this->assertCount(\count(ScopeEnum::ALL), $options['scopes']);

        $firstScope = $options['scopes'][0];
        $this->assertArrayHasKey('code', $firstScope);
        $this->assertArrayHasKey('label', $firstScope);
        $this->assertSame(ScopeEnum::DEPUTY, $firstScope['code']);
        $this->assertSame('Député', $firstScope['label']);
    }

    public function testBuildContainsTeamRolesOption(): void
    {
        $filters = $this->builder->build(ScopeEnum::NATIONAL);
        $filter = $filters[0];
        $options = $filter->getOptions();

        $this->assertArrayHasKey('team_roles', $options);
        $this->assertIsArray($options['team_roles']);
        $this->assertCount(\count(RoleEnum::LABELS), $options['team_roles']);

        $firstRole = $options['team_roles'][0];
        $this->assertArrayHasKey('code', $firstRole);
        $this->assertArrayHasKey('label', $firstRole);
        $this->assertSame(RoleEnum::GENERAL_SECRETARY, $firstRole['code']);
        $this->assertSame('Secrétaire général', $firstRole['label']);
    }

    public function testBuildContainsAllowCustomRoleOption(): void
    {
        $filters = $this->builder->build(ScopeEnum::NATIONAL);
        $filter = $filters[0];
        $options = $filter->getOptions();

        $this->assertArrayHasKey('allow_custom_role', $options);
        $this->assertTrue($options['allow_custom_role']);
    }

    public function testGetGroupReturnsScopeTargetFilterGroup(): void
    {
        $this->assertSame(
            ScopeTargetFilterGroup::class,
            $this->builder->getGroup(ScopeEnum::NATIONAL)
        );
    }
}

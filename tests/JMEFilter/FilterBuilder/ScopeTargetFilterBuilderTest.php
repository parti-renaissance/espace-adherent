<?php

declare(strict_types=1);

namespace Tests\App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterBuilder\ScopeTargetFilterBuilder;
use App\JMEFilter\Types\DefinedTypes\ScopeTarget;
use App\MyTeam\RoleEnum;
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
                    'scope.role.legislative_candidate' => 'Candidat',
                    'scope.role.national' => 'Rôle national',
                ];

                return $translations[$key] ?? $key;
            })
        ;

        $this->builder = new ScopeTargetFilterBuilder($translator);
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
        $this->assertSame(ScopeEnum::LEGISLATIVE_CANDIDATE, $firstScope['code']);
        $this->assertSame('Candidat', $firstScope['label']);
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
}

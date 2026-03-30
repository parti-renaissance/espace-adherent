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

    public function testBuildContainsInstancesOption(): void
    {
        $filters = $this->builder->build(ScopeEnum::NATIONAL);
        $filter = $filters[0];
        $options = $filter->getOptions();

        $this->assertArrayHasKey('instances', $options);
        $this->assertIsArray($options['instances']);
        $this->assertCount(\count(ScopeEnum::SCOPE_TARGET_CHOICES), $options['instances']);

        $firstInstance = $options['instances'][0];
        $this->assertArrayHasKey('name', $firstInstance);
        $this->assertArrayHasKey('code', $firstInstance);
        $this->assertArrayHasKey('main_role', $firstInstance);
        $this->assertArrayHasKey('team_roles', $firstInstance);

        $this->assertSame(ScopeEnum::LEGISLATIVE_CANDIDATE, $firstInstance['code']);
        $this->assertSame('Candidat', $firstInstance['main_role']);
        $this->assertSame('Circonscription', $firstInstance['name']);

        // Each instance has all team roles + custom role
        $this->assertCount(\count(RoleEnum::LABELS) + 1, $firstInstance['team_roles']);

        $firstRole = $firstInstance['team_roles'][0];
        $this->assertArrayHasKey('code', $firstRole);
        $this->assertArrayHasKey('label', $firstRole);
        $this->assertSame(RoleEnum::GENERAL_SECRETARY, $firstRole['code']);
        $this->assertSame('Secrétaire général', $firstRole['label']);

        $lastRole = end($firstInstance['team_roles']);
        $this->assertSame(RoleEnum::CUSTOM_ROLE, $lastRole['code']);
        $this->assertSame('Rôle personnalisé', $lastRole['label']);
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

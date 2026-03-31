<?php

declare(strict_types=1);

namespace Tests\App\JMEFilter\FilterBuilder;

use App\Entity\Scope;
use App\JMEFilter\FilterBuilder\ScopeTargetFilterBuilder;
use App\JMEFilter\Types\DefinedTypes\ScopeTarget;
use App\MyTeam\RoleEnum;
use App\Repository\ScopeRepository;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScopeTargetFilterBuilderTest extends TestCase
{
    private ScopeTargetFilterBuilder $builder;

    protected function setUp(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
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

        $scopes = [];
        foreach (ScopeEnum::SCOPE_TARGET_CHOICES as $code) {
            $scope = new Scope($code, ucfirst(str_replace('_', ' ', $code)));

            if (ScopeEnum::LEGISLATIVE_CANDIDATE === $code) {
                // MY_TEAM + MY_TEAM_CUSTOM_ROLE
                $scope->setFeatures([FeatureEnum::MY_TEAM, FeatureEnum::MY_TEAM_CUSTOM_ROLE]);
            } elseif (\in_array($code, [ScopeEnum::CORRESPONDENT, ScopeEnum::ANIMATOR], true)) {
                // MY_TEAM only (no custom role)
                $scope->setFeatures([FeatureEnum::MY_TEAM]);
            }

            $scopes[] = $scope;
        }

        $scopeRepository = $this->createStub(ScopeRepository::class);
        $scopeRepository
            ->method('findBy')
            ->willReturn($scopes)
        ;

        $this->builder = new ScopeTargetFilterBuilder($translator, $scopeRepository);
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

        $this->assertSame(['instances'], array_keys($options));
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

        // legislative_candidate has MY_TEAM + MY_TEAM_CUSTOM_ROLE → all roles + custom
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

    public function testInstanceWithoutMyTeamFeatureHasEmptyTeamRoles(): void
    {
        $filters = $this->builder->build(ScopeEnum::NATIONAL);
        $options = $filters[0]->getOptions();

        $instances = array_column($options['instances'], null, 'code');

        $this->assertArrayHasKey(ScopeEnum::DEPUTY, $instances);
        $this->assertSame([], $instances[ScopeEnum::DEPUTY]['team_roles']);
    }

    public function testInstanceWithMyTeamButWithoutCustomRoleExcludesCustomRole(): void
    {
        $filters = $this->builder->build(ScopeEnum::NATIONAL);
        $options = $filters[0]->getOptions();

        $instances = array_column($options['instances'], null, 'code');

        // correspondent has MY_TEAM but NOT MY_TEAM_CUSTOM_ROLE
        $this->assertArrayHasKey(ScopeEnum::CORRESPONDENT, $instances);
        $teamRoles = $instances[ScopeEnum::CORRESPONDENT]['team_roles'];

        // Standard roles only, no custom
        $this->assertCount(\count(RoleEnum::LABELS), $teamRoles);
        $codes = array_column($teamRoles, 'code');
        $this->assertNotContains(RoleEnum::CUSTOM_ROLE, $codes);
    }

    public function testInstanceNameFallsBackToScopeEntityName(): void
    {
        $filters = $this->builder->build(ScopeEnum::NATIONAL);
        $options = $filters[0]->getOptions();

        $instances = array_column($options['instances'], null, 'code');

        // Scopes in SCOPE_INSTANCES get their hardcoded name
        $this->assertSame('Circonscription', $instances[ScopeEnum::LEGISLATIVE_CANDIDATE]['name']);

        // Scopes NOT in SCOPE_INSTANCES fallback to entity name
        if (\in_array(ScopeEnum::SENATOR, ScopeEnum::SCOPE_TARGET_CHOICES, true)) {
            $this->assertNotNull($instances[ScopeEnum::SENATOR]['name']);
            $this->assertSame('Senator', $instances[ScopeEnum::SENATOR]['name']);
        }
    }
}

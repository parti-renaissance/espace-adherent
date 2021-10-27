<?php

namespace Tests\App\Scope;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Repository\AdherentRepository;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class GeneralScopeGeneratorTest extends AbstractKernelTestCase
{
    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var GeneralScopeGenerator
     */
    private $generalScopeGenerator;

    /** @dataProvider provideAdherent */
    public function testGenerateScopes(string $adherentUuid, array $expectedScopes): void
    {
        $adherent = $this->adherentRepository->findOneByUuid($adherentUuid);
        $scopes = $this->generalScopeGenerator->generateScopes($adherent);

        $this->assertCount(\count($expectedScopes), $scopes);
        foreach ($scopes as $key => $scope) {
            $this->checkScope($scope, $expectedScopes[$key]);
        }
    }

    public function provideAdherent(): iterable
    {
        yield [LoadAdherentData::DEPUTY_1_UUID, [
            [
                'code' => 'deputy',
                'zones' => [[
                    'code' => '75-1',
                    'name' => 'Paris (1)',
                ]],
                'apps' => ['data_corner'],
            ],
            [
                'code' => 'national',
                'zones' => [[
                    'code' => 'FR',
                    'name' => 'France',
                ]],
                'apps' => ['data_corner'],
            ],
            [
                'code' => 'pap_national_manager',
                'zones' => [],
                'apps' => ['data_corner'],
                'features' => ['pap'],
            ],
            [
                'code' => 'phoning_national_manager',
                'zones' => [[
                    'code' => 'FR',
                    'name' => 'France',
                ]],
                'apps' => ['data_corner'],
            ],
            [
                'code' => 'phoning',
                'zones' => [],
                'apps' => ['jemarche'],
            ],
        ]];
        yield [LoadAdherentData::REFERENT_3_UUID, [[
            'code' => 'referent',
            'zones' => [[
                'code' => '93',
                'name' => 'Seine-Saint-Denis',
            ], [
                'code' => 'CH',
                'name' => 'Suisse',
            ]],
            'apps' => ['data_corner'],
        ]]];
        yield [LoadAdherentData::SENATOR_UUID, [
            [
                'code' => 'senator',
                'zones' => [[
                    'code' => '59',
                    'name' => 'Nord',
                ]],
                'apps' => ['data_corner'],
            ],
            [
                'code' => 'delegated_08f40730-d807-4975-8773-69d8fae1da74',
                'zones' => [
                    [
                        'code' => '13',
                        'name' => 'Bouches-du-Rhône',
                    ],
                    [
                        'code' => '59',
                        'name' => 'Nord',
                    ],
                    [
                        'code' => '76',
                        'name' => 'Seine-Maritime',
                    ],
                    [
                        'code' => '77',
                        'name' => 'Seine-et-Marne',
                    ],
                    [
                        'code' => '92',
                        'name' => 'Hauts-de-Seine',
                    ],
                    [
                        'code' => 'ES',
                        'name' => 'Espagne',
                    ],
                    [
                        'code' => 'CH',
                        'name' => 'Suisse',
                    ],
                ],
                'apps' => ['data_corner'],
            ],
        ]];
        yield [LoadAdherentData::ADHERENT_3_UUID, [
            [
                'code' => 'candidate',
                'zones' => [[
                    'code' => '11',
                    'name' => 'Île-de-France',
                ]],
                'apps' => [],
            ],
            [
                'code' => 'phoning',
                'zones' => [],
                'apps' => ['jemarche'],
            ],
        ]];
        yield [LoadAdherentData::ADHERENT_2_UUID, []];
    }

    private function checkScope(Scope $scope, array $expectedScope): void
    {
        $this->assertSame($expectedScope['code'], $scope->getCode());
        $this->assertSame(\count($expectedScope['zones']), \count($scope->getZones()));
        foreach ($scope->getZones() as $key => $zone) {
            $this->assertSame($expectedScope['zones'][$key]['code'], $zone->getCode());
            $this->assertSame($expectedScope['zones'][$key]['name'], $zone->getName());
        }
        $this->assertSame($expectedScope['apps'], $scope->getApps());
        if (isset($expectedScope['features'])) {
            $this->assertSame($expectedScope['features'], $scope->getFeatures());
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->generalScopeGenerator = $this->get(GeneralScopeGenerator::class);
        $this->adherentRepository = $this->get(AdherentRepository::class);
    }

    protected function tearDown(): void
    {
        $this->generalScopeGenerator = null;
        $this->adherentRepository = null;

        parent::tearDown();
    }
}

<?php

namespace Tests\App\Scope;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Repository\AdherentRepository;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
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

    #[DataProvider('provideAdherent')]
    public function testGenerateScopes(string $adherentUuid, array $expectedScopes): void
    {
        $adherent = $this->adherentRepository->findOneByUuid($adherentUuid);
        $scopes = $this->generalScopeGenerator->generateScopes($adherent);

        foreach ($scopes as $key => $scope) {
            $this->checkScope($scope, $expectedScopes[$key]);
        }
        $this->assertCount(\count($expectedScopes), $scopes);
    }

    public static function provideAdherent(): iterable
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
                'code' => 'national_communication',
                'zones' => [[
                    'code' => 'FR',
                    'name' => 'France',
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
                'zones' => [[
                    'code' => 'FR',
                    'name' => 'France',
                ]],
                'apps' => ['data_corner'],
                'features' => ['pap'],
            ],
            [
                'code' => 'pap',
                'zones' => [],
                'apps' => ['jemarche'],
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
        yield [LoadAdherentData::ADHERENT_3_UUID, [
            [
                'code' => 'agora_general_secretary',
                'zones' => [],
                'apps' => ['data_corner'],
            ],
            [
                'code' => 'agora_president',
                'zones' => [],
                'apps' => ['data_corner'],
            ],
            [
                'code' => 'candidate',
                'zones' => [
                    [
                        'code' => '11',
                        'name' => 'Île-de-France',
                    ],
                ],
                'apps' => ['data_corner'],
            ],
            [
                'code' => 'pap',
                'zones' => [],
                'apps' => ['jemarche'],
            ],
            [
                'code' => 'phoning',
                'zones' => [],
                'apps' => ['jemarche'],
            ],
            [
                'code' => 'delegated_433e368f-fd4e-4a24-9f01-b667f8e3b9f2',
                'zones' => [
                    [
                        'code' => '77',
                        'name' => 'Seine-et-Marne',
                    ],
                    [
                        'code' => '92',
                        'name' => 'Hauts-de-Seine',
                    ],
                    [
                        'code' => '76',
                        'name' => 'Seine-Maritime',
                    ],
                    [
                        'code' => '59',
                        'name' => 'Nord',
                    ],
                    [
                        'code' => '13',
                        'name' => 'Bouches-du-Rhône',
                    ],
                ],
                'apps' => ['data_corner'],
                'features' => ['dashboard', 'contacts', 'events', 'mobile_app'],
            ],
        ]];
        yield [LoadAdherentData::ADHERENT_2_UUID, []];
        yield [LoadAdherentData::ADHERENT_20_UUID, [
            [
                'code' => 'fde_coordinator',
                'zones' => [[
                    'code' => 'CIRCO_FDE-06',
                    'name' => 'Suisse',
                ]],
                'apps' => ['data_corner'],
            ],
            [
                'code' => 'meeting_scanner',
                'zones' => [],
                'apps' => ['jemarche'],
            ],
            [
                'code' => 'municipal_pilot',
                'zones' => [[
                    'code' => '75056',
                    'name' => 'Paris',
                ]],
                'apps' => ['data_corner'],
            ],
            [
                'code' => 'president_departmental_assembly',
                'zones' => [[
                    'code' => '92',
                    'name' => 'Hauts-de-Seine',
                ]],
                'apps' => ['data_corner'],
            ],
            [
                'code' => 'procurations_manager',
                'zones' => [[
                    'code' => '92',
                    'name' => 'Hauts-de-Seine',
                ]],
                'apps' => ['data_corner'],
            ],
        ]];
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

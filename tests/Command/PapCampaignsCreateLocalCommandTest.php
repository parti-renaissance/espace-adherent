<?php

namespace Tests\App\Command;

use App\Entity\Pap\Campaign;
use App\Repository\Pap\CampaignRepository;
use App\Scope\ScopeVisibilityEnum;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\App\AbstractCommandCaseTest;
use Tests\App\TestHelperTrait;

/**
 * @group functional
 */
class PapCampaignsCreateLocalCommandTest extends AbstractCommandCaseTest
{
    use TestHelperTrait;

    private ?CampaignRepository $campaignRepository = null;

    public function testExecuteWithCode(): void
    {
        self::assertNull($this->campaignRepository->findOneBy([
            'title' => 'Campagne de la circonscription Hauts-de-Seine (1) 92-1 ',
        ]));

        $tester = new CommandTester($this->application->find('app:pap:create-local-campaign'));

        $tester->execute([
            '--code' => '92-1',
        ]);

        $output = $tester->getDisplay();

        self::assertStringContainsString('1 local PAP campaigns created successfully!', $output);

        $campaign = $this->campaignRepository->findOneBy([
            'title' => 'Campagne de la circonscription Hauts-de-Seine (1) 92-1 ',
        ]);

        self::assertInstanceOf(Campaign::class, $campaign);
        self::assertSame(ScopeVisibilityEnum::LOCAL, $campaign->getVisibility());
        self::assertCount(1, $campaign->getZones());

        $zone = $campaign->getZones()->first();

        self::assertSame('92-1', $zone->getCode());
        self::assertLessThan(new \DateTime('now'), $campaign->getBeginAt());
        self::assertGreaterThan(new \DateTime('now'), $campaign->getFinishAt());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->campaignRepository = $this->getRepository(Campaign::class);
    }

    protected function tearDown(): void
    {
        $this->campaignRepository = null;

        parent::tearDown();
    }
}

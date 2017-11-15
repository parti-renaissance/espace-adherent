<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadPurchasingPowerData;
use AppBundle\Entity\PurchasingPowerChoice;
use AppBundle\Repository\PurchasingPowerChoiceRepository;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class PurchasingPowerChoiceRepositoryTest extends SqliteWebTestCase
{
    /**
     * @var PurchasingPowerChoiceRepository
     */
    private $repository;

    use ControllerTestTrait;

    public function testGetMailIntroductionAndConclusion()
    {
        $introduction = $this->repository->findMailIntroduction();

        $this->assertInstanceOf(PurchasingPowerChoice::class, $introduction);
        $this->assertSame('28ceb6d3-ec64-4a58-99a4-71357600d07c', $introduction->getUuid()->toString());

        $conclusion = $this->repository->findMailConclusion();

        $this->assertInstanceOf(PurchasingPowerChoice::class, $conclusion);
        $this->assertSame('3d735d18-348c-4d02-8046-7976f86e5ecc', $conclusion->getUuid()->toString());

        $common = $this->repository->findMailCommon();

        $this->assertInstanceOf(PurchasingPowerChoice::class, $common);
        $this->assertSame('a642dbc7-aba5-49e4-877a-06bc1ef23168', $common->getUuid()->toString());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadPurchasingPowerData::class,
        ]);

        $this->container = $this->getContainer();
        $this->repository = $this->getPurchasingPowerChoiceRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }
}

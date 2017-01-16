<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Repository\AdherentRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

class AdherentRepositoryTest extends WebTestCase
{
    /**
     * @var AdherentRepository
     */
    private $repository;

    use ControllerTestTrait;

    public function testLoadUserByUsername()
    {
        $this->assertInstanceOf(
            Adherent::class,
            $this->repository->loadUserByUsername('carl999@example.fr'),
            'Enabled adherent must be returned.'
        );

        $this->assertNull(
            $this->repository->loadUserByUsername('michelle.dufour@example.ch'),
            'Disabled adherent must not be returned.'
        );

        $this->assertNull(
            $this->repository->loadUserByUsername('someone@foobar.tld'),
            'Non registered adherent must not be returned.'
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->container = $this->getContainer();
        $this->repository = $this->getAdherentRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }
}

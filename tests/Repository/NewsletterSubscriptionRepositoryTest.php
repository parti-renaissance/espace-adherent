<?php

namespace Tests\App\Repository;

use App\Repository\NewsletterSubscriptionRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class NewsletterSubscriptionRepositoryTest extends WebTestCase
{
    /** @var NewsletterSubscriptionRepository */
    private $repository;

    use ControllerTestTrait;

    public function testFindAllManagedBy()
    {
        $referent = $this->getAdherentRepository()->loadUserByUsername('referent@en-marche-dev.fr');

        $managedByReferent = $this->repository->findAllManagedBy($referent);

        $this->assertCount(3, $managedByReferent, 'Referent should manage 3 newsletter subscribers in his area.');
        $this->assertSame('abc@en-marche-dev.fr', $managedByReferent[0]->getEmail());
        $this->assertSame('def@en-marche-dev.fr', $managedByReferent[1]->getEmail());
        $this->assertSame('ghi@en-marche-dev.fr', $managedByReferent[2]->getEmail());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->getContainer();
        $this->repository = $this->getNewsletterSubscriptionRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }
}

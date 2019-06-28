<?php

namespace Tests\AppBundle\Repository;

use AppBundle\Repository\NewsletterSubscriptionRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class NewsletterSubscriptionRepositoryTest extends WebTestCase
{
    use ControllerTestTrait;

    /** @var NewsletterSubscriptionRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->getContainer();
        $this->repository = $this->getNewsletterSubscriptionRepository();
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }

    public function testFindAllManagedBy()
    {
        $referent = $this->getAdherentRepository()->loadUserByUsername('referent@en-marche-dev.fr');

        $managedByReferent = $this->repository->findAllManagedBy($referent);

        $this->assertCount(3, $managedByReferent, 'Referent should manage 3 newsletter subscribers in his area.');
        $this->assertSame('abc@en-marche-dev.fr', $managedByReferent[0]->getEmail());
        $this->assertSame('def@en-marche-dev.fr', $managedByReferent[1]->getEmail());
        $this->assertSame('ghi@en-marche-dev.fr', $managedByReferent[2]->getEmail());
    }
}

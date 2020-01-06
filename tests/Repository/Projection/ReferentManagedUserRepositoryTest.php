<?php

namespace Tests\AppBundle\Repository;

use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Entity\ReferentTag;
use AppBundle\ManagedUsers\ManagedUsersFilter;
use AppBundle\Repository\Projection\ReferentManagedUserRepository;
use AppBundle\Subscription\SubscriptionTypeEnum;
use Doctrine\Common\Persistence\ObjectRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group referent
 */
class ReferentManagedUserRepositoryTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var ReferentManagedUserRepository
     */
    private $referentManagedUserRepository;

    /**
     * @var ObjectRepository
     */
    private $referentTagRepository;

    public function testSearch()
    {
        $filter = new ManagedUsersFilter(null, [
            $this->referentTagRepository->findOneBy(['code' => 'ch']),
            $this->referentTagRepository->findOneBy(['code' => '77']),
        ]);

        $this->assertCount(3, $this->referentManagedUserRepository->searchByFilter($filter));
    }

    /**
     * @dataProvider providesOnlyEmailSubscribers
     */
    public function testSearchWithEmailSubscribersInevitably(?bool $onlyEmailSubscribers, int $count)
    {
        $filter = new ManagedUsersFilter(SubscriptionTypeEnum::REFERENT_EMAIL, [
            $this->referentTagRepository->findOneBy(['code' => 'ch']),
            $this->referentTagRepository->findOneBy(['code' => '77']),
        ]);
        $filter->setEmailSubscription($onlyEmailSubscribers);

        $this->assertCount($count, $this->referentManagedUserRepository->searchByFilter($filter));
    }

    public function providesOnlyEmailSubscribers(): \Generator
    {
        yield [null, 3];
        yield [true, 2];
        yield [false, 1];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->getContainer();
        $this->referentManagedUserRepository = $this->getRepository(ReferentManagedUser::class);
        $this->referentTagRepository = $this->getRepository(ReferentTag::class);
    }

    protected function tearDown()
    {
        $this->kill();

        $this->referentManagedUserRepository = null;
        $this->referentTagRepository = null;

        parent::tearDown();
    }
}

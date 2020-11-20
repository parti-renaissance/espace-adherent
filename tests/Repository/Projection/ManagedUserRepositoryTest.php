<?php

namespace Tests\App\Repository;

use App\Entity\Geo\Zone;
use App\Entity\Projection\ManagedUser;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\Projection\ManagedUserRepository;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\Common\Persistence\ObjectRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group referent
 */
class ManagedUserRepositoryTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var ManagedUserRepository
     */
    private $managedUserRepository;

    /**
     * @var ObjectRepository
     */
    private $zoneRepository;

    public function testSearch()
    {
        $filter = new ManagedUsersFilter(null, [
            $this->zoneRepository->findOneBy(['code' => 'CH', 'type' => Zone::COUNTRY]),
            $this->zoneRepository->findOneBy(['code' => '77', 'type' => Zone::DEPARTMENT]),
        ]);

        $this->assertCount(3, $this->managedUserRepository->searchByFilter($filter));
    }

    /**
     * @dataProvider providesOnlyEmailSubscribers
     */
    public function testSearchWithEmailSubscribersInevitably(?bool $onlyEmailSubscribers, int $count)
    {
        $filter = new ManagedUsersFilter(SubscriptionTypeEnum::REFERENT_EMAIL, [
            $this->zoneRepository->findOneBy(['code' => 'CH', 'type' => Zone::COUNTRY]),
            $this->zoneRepository->findOneBy(['code' => '77', 'type' => Zone::DEPARTMENT]),
        ]);
        $filter->setEmailSubscription($onlyEmailSubscribers);

        $this->assertCount($count, $this->managedUserRepository->searchByFilter($filter));
    }

    public function providesOnlyEmailSubscribers(): \Generator
    {
        yield [null, 3];
        yield [true, 1];
        yield [false, 2];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->getContainer();
        $this->managedUserRepository = $this->getRepository(ManagedUser::class);
        $this->zoneRepository = $this->getRepository(Zone::class);
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->managedUserRepository = null;

        parent::tearDown();
    }
}

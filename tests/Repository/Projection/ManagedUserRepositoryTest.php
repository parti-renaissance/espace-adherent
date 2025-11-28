<?php

declare(strict_types=1);

namespace Tests\App\Repository\Projection;

use App\Entity\Geo\Zone;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Projection\ManagedUserRepository;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
#[Group('referent')]
class ManagedUserRepositoryTest extends AbstractKernelTestCase
{
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

    #[DataProvider('providesOnlyEmailSubscribers')]
    public function testSearchWithEmailSubscribersInevitably(?bool $onlyEmailSubscribers, int $count)
    {
        $filter = new ManagedUsersFilter(SubscriptionTypeEnum::REFERENT_EMAIL, [
            $this->zoneRepository->findOneBy(['code' => 'CH', 'type' => Zone::COUNTRY]),
            $this->zoneRepository->findOneBy(['code' => '77', 'type' => Zone::DEPARTMENT]),
        ]);
        $filter->emailSubscription = $onlyEmailSubscribers;

        $this->assertCount($count, $this->managedUserRepository->searchByFilter($filter));
    }

    public static function providesOnlyEmailSubscribers(): \Generator
    {
        yield [null, 3];
        yield [true, 1];
        yield [false, 2];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->managedUserRepository = $this->get(ManagedUserRepository::class);
        $this->zoneRepository = $this->get(ZoneRepository::class);
    }

    protected function tearDown(): void
    {
        $this->managedUserRepository = null;
        $this->zoneRepository = null;

        parent::tearDown();
    }
}

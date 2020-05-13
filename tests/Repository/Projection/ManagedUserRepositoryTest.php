<?php

namespace Tests\App\Repository;

use App\Entity\Projection\ManagedUser;
use App\Entity\ReferentTag;
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
    private $referentTagRepository;

    public function testSearch()
    {
        $filter = new ManagedUsersFilter(null, [
            $this->referentTagRepository->findOneBy(['code' => 'ch']),
            $this->referentTagRepository->findOneBy(['code' => '77']),
        ]);

        $this->assertCount(3, $this->managedUserRepository->searchByFilter($filter));
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

        $this->assertCount($count, $this->managedUserRepository->searchByFilter($filter));
    }

    public function providesOnlyEmailSubscribers(): \Generator
    {
        yield [null, 3];
        yield [true, 1];
        yield [false, 2];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->getContainer();
        $this->managedUserRepository = $this->getRepository(ManagedUser::class);
        $this->referentTagRepository = $this->getRepository(ReferentTag::class);
    }

    protected function tearDown()
    {
        $this->kill();

        $this->managedUserRepository = null;
        $this->referentTagRepository = null;

        parent::tearDown();
    }
}

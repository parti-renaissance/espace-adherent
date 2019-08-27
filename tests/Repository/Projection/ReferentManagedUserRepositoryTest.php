<?php

namespace Tests\AppBundle\Repository;

use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Entity\ReferentTag;
use AppBundle\Referent\ManagedUsersFilter;
use AppBundle\Repository\Projection\ReferentManagedUserRepository;
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
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent(
            [
                $this->referentTagRepository->findOneBy(['code' => 'CH']),
                $this->referentTagRepository->findOneBy(['code' => '77']),
            ],
            '1.123456',
            '2.34567'
        );

        $this->assertCount(3, $this->referentManagedUserRepository->search($referent));
    }

    public function testSearchWithoutEmailSubscribers()
    {
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent(
            [
                $this->referentTagRepository->findOneBy(['code' => 'CH']),
                $this->referentTagRepository->findOneBy(['code' => '77']),
            ],
            '1.123456',
            '2.34567'
        );

        $filter = $this->createMock(ManagedUsersFilter::class);
        $filter->expects($this->any())->method('onlyEmailSubscribers')->willReturn(false);

        $this->assertCount(1, $this->referentManagedUserRepository->search($referent, $filter));
    }

    /**
     * @dataProvider providesOnlyEmailSubscribers
     */
    public function testSearchWithEmailSubscribersInevitably(?bool $onlyEmailSubscribers, int $count)
    {
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent(
            [
                $this->referentTagRepository->findOneBy(['code' => 'CH']),
                $this->referentTagRepository->findOneBy(['code' => '77']),
            ],
            '1.123456',
            '2.34567'
        );

        $filter = $this->createMock(ManagedUsersFilter::class);
        $filter->expects($this->any())->method('onlyEmailSubscribers')->willReturn($onlyEmailSubscribers);

        $this->assertCount($count, $this->referentManagedUserRepository->search($referent, $filter, true));
    }

    public function providesOnlyEmailSubscribers(): \Generator
    {
        yield [null, 2];
        yield [true, 2];
        yield [false, 0];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSearchWithInvalidReferent()
    {
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent([], '1.123456', '2.34567');

        $this->referentManagedUserRepository->search($referent);
    }

    public function testCreateDispatcherIterator()
    {
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent(
            [
                $this->referentTagRepository->findOneBy(['code' => '92']),
                $this->referentTagRepository->findOneBy(['code' => '77']),
            ],
            '1.123456',
            '2.34567'
        );

        $results = $this->referentManagedUserRepository->createDispatcherIterator($referent);

        $expectedEmails = ['francis.brioul@yahoo.com', 'gisele-berthoux@caramail.com'];

        $count = 0;
        foreach ($results as $key => $result) {
            $this->assertSame($expectedEmails[$key], $result[0]->getEmail());
            ++$count;
        }

        $this->assertSame(2, $count);
    }

    public function testCreateDispatcherIteratorWithOffset()
    {
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent(
            [
                $this->referentTagRepository->findOneBy(['code' => '92']),
                $this->referentTagRepository->findOneBy(['code' => '77']),
            ],
            '1.123456',
            '2.34567'
        );

        $filter = $this->createMock(ManagedUsersFilter::class);
        $filter->expects($this->once())->method('getOffset')->willReturn(1);

        $results = $this->referentManagedUserRepository->createDispatcherIterator($referent, $filter);

        $expectedEmails = ['gisele-berthoux@caramail.com'];

        $count = 0;
        foreach ($results as $key => $result) {
            $this->assertSame($expectedEmails[$key], $result[0]->getEmail());
            ++$count;
        }

        $this->assertSame(1, $count);
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

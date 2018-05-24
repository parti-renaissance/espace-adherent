<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Repository\EventRepository;
use AppBundle\Search\SearchParametersFilter;
use Symfony\Component\HttpFoundation\Request;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class EventRepositoryTest extends MysqlWebTestCase
{
    /**
     * @var EventRepository
     */
    private $repository;

    use ControllerTestTrait;

    public function testCountEvents()
    {
        $this->assertSame(19, $this->repository->countElements());
    }

    public function testFindUpcomingEvents()
    {
        $this->assertCount(9, $this->repository->findUpcomingEvents());
    }

    public function testCountUpcomingEvents()
    {
        $this->assertSame(9, $this->repository->countUpcomingEvents());
    }

    public function testSearchAllEvents()
    {
        $query = [
            'q' => '',
            'r' => '150',
            'c' => 'paris',
            't' => 'events',
        ];
        $request = new Request($query);
        $search = $this->get(SearchParametersFilter::class)->handleRequest($request);

        $this->assertSame(5, count($this->repository->searchAllEvents($search)));

        $query = [
            'q' => '',
            'r' => '150',
            'c' => 'paris',
            't' => 'events',
            'offset' => '0',
            'ec' => $this->getEventCategoryIdForName(LoadEventCategoryData::LEGACY_EVENT_CATEGORIES['CE003']),
        ];
        $request = new Request($query);
        $search = $this->get(SearchParametersFilter::class)->handleRequest($request);

        $this->assertSame(1, count($this->repository->searchAllEvents($search)));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadEventData::class,
        ]);

        $this->repository = $this->getEventRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->repository = null;

        parent::tearDown();
    }
}

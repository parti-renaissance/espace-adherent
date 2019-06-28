<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\Repository\EventRepository;
use AppBundle\Search\SearchParametersFilter;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class EventRepositoryTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var EventRepository
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->repository = $this->getEventRepository();
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->repository = null;

        parent::tearDown();
    }

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

        $this->assertSame(8, \count($this->repository->searchAllEvents($search)));

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

        $this->assertSame(1, \count($this->repository->searchAllEvents($search)));

        $query = [
            'q' => '',
            'r' => '150',
            'c' => 'paris',
            't' => 'citizen_actions',
            'offset' => '0',
            'ec' => 'citizen_actions',
        ];
        $request = new Request($query);
        $search = $this->get(SearchParametersFilter::class)->handleRequest($request);

        $this->assertSame(2, \count($this->repository->searchAllEvents($search)));
    }
}

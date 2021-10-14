<?php

namespace Tests\App\Repository;

use App\DataFixtures\ORM\LoadEventCategoryData;
use App\Repository\EventRepository;
use App\Search\SearchParametersFilter;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class EventRepositoryTest extends AbstractKernelTestCase
{
    /**
     * @var EventRepository
     */
    private $repository;

    public function testCountEvents()
    {
        $this->assertSame(19, $this->repository->countElements(true, false));
        $this->assertSame(20, $this->repository->countElements(true, true));
    }

    public function testFindUpcomingEvents()
    {
        $this->assertCount(10, $this->repository->findUpcomingEvents(null, true));
        $this->assertCount(9, $this->repository->findUpcomingEvents(null, false));
    }

    public function testCountUpcomingEvents()
    {
        $this->assertSame(10, $this->repository->countUpcomingEvents(true));
        $this->assertSame(9, $this->repository->countUpcomingEvents(false));
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

        $this->assertSame(5, \count($this->repository->searchAllEvents($search)));

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
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getEventRepository();
    }

    protected function tearDown(): void
    {
        $this->repository = null;

        parent::tearDown();
    }
}

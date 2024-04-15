<?php

namespace Tests\App\Repository;

use App\DataFixtures\ORM\LoadEventCategoryData;
use App\Repository\EventRepository;
use App\Search\SearchParametersFilter;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class EventRepositoryTest extends AbstractKernelTestCase
{
    /**
     * @var EventRepository
     */
    private $repository;

    public function testCountEvents()
    {
        $this->assertSame(18, $this->repository->countElements(true, false));
        $this->assertSame(19, $this->repository->countElements(true, true));

        // Renaissance event
        $this->assertSame(2, $this->repository->countElements(true, false, true));
        $this->assertSame(2, $this->repository->countElements(true, true, true));
    }

    public function testCountUpcomingEvents()
    {
        Chronos::setTestNow('2018-05-18');

        $this->assertSame(9, $this->repository->countUpcomingEvents(true));
        $this->assertSame(8, $this->repository->countUpcomingEvents(false));

        Chronos::setTestNow();
    }

    public function testSearchAllEvents()
    {
        Chronos::setTestNow('2018-05-18');

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

        Chronos::setTestNow();
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

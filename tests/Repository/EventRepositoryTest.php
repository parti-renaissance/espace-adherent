<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\DataFixtures\ORM\LoadEventCategoryData;
use App\Repository\Event\EventRepository;
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

        $this->assertSame(2, \count($this->repository->searchAllEvents($search)));

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

<?php

declare(strict_types=1);

namespace Tests\App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Api\Filter\EventBoundingBoxFilter;
use App\Entity\Event\Event;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
class EventBoundingBoxFilterTest extends TestCase
{
    private EventBoundingBoxFilter $filter;

    protected function setUp(): void
    {
        $this->filter = new EventBoundingBoxFilter();
    }

    public function testFilterWithValidCornersAddsBetweenConstraint(): void
    {
        $andWhereCalls = [];
        $setParameterCalls = [];
        $qb = $this->createQueryBuilderMock($andWhereCalls, $setParameterCalls);

        $this->applyFilter($qb, ['ne' => ['lat' => '49.5', 'lng' => '3.25'], 'sw' => ['lat' => '48.0', 'lng' => '2.0']]);

        self::assertCount(1, $andWhereCalls);
        self::assertSame(
            'e.postAddress.latitude BETWEEN :bbox_min_lat AND :bbox_max_lat AND e.postAddress.longitude BETWEEN :bbox_min_lng AND :bbox_max_lng',
            $andWhereCalls[0]
        );
        self::assertSame(48.0, $setParameterCalls['bbox_min_lat']);
        self::assertSame(49.5, $setParameterCalls['bbox_max_lat']);
        self::assertSame(2.0, $setParameterCalls['bbox_min_lng']);
        self::assertSame(3.25, $setParameterCalls['bbox_max_lng']);
    }

    public function testFilterWithSwappedCornersNormalisesBounds(): void
    {
        $andWhereCalls = [];
        $setParameterCalls = [];
        $qb = $this->createQueryBuilderMock($andWhereCalls, $setParameterCalls);

        // North-east carries the smaller values: the filter must still produce a valid range.
        $this->applyFilter($qb, ['ne' => ['lat' => '48.0', 'lng' => '2.0'], 'sw' => ['lat' => '49.5', 'lng' => '3.25']]);

        self::assertCount(1, $andWhereCalls);
        self::assertSame(48.0, $setParameterCalls['bbox_min_lat']);
        self::assertSame(49.5, $setParameterCalls['bbox_max_lat']);
        self::assertSame(2.0, $setParameterCalls['bbox_min_lng']);
        self::assertSame(3.25, $setParameterCalls['bbox_max_lng']);
    }

    public function testFilterWithIncompleteBoundingBoxIsIgnored(): void
    {
        $andWhereCalls = [];
        $qb = $this->createQueryBuilderMock($andWhereCalls);

        // Missing sw.lng.
        $this->applyFilter($qb, ['ne' => ['lat' => '49.5', 'lng' => '3.25'], 'sw' => ['lat' => '48.0']]);

        self::assertSame([], $andWhereCalls);
    }

    public function testFilterWithNonNumericValueIsIgnored(): void
    {
        $andWhereCalls = [];
        $qb = $this->createQueryBuilderMock($andWhereCalls);

        $this->applyFilter($qb, ['ne' => ['lat' => 'north', 'lng' => '3.25'], 'sw' => ['lat' => '48.0', 'lng' => '2.0']]);

        self::assertSame([], $andWhereCalls);
    }

    public function testFilterWithNonArrayValueIsIgnored(): void
    {
        $andWhereCalls = [];
        $qb = $this->createQueryBuilderMock($andWhereCalls);

        $this->applyFilter($qb, '48.0,2.0,49.5,3.25');

        self::assertSame([], $andWhereCalls);
    }

    public function testFilterWithUnrelatedPropertyIsIgnored(): void
    {
        $andWhereCalls = [];
        $qb = $this->createQueryBuilderMock($andWhereCalls);

        $this->filter->apply(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            Event::class,
            null,
            ['filters' => ['somethingElse' => ['ne' => ['lat' => '49.5', 'lng' => '3.25'], 'sw' => ['lat' => '48.0', 'lng' => '2.0']]]]
        );

        self::assertSame([], $andWhereCalls);
    }

    public function testFilterWithUnrelatedResourceClassIsIgnored(): void
    {
        $andWhereCalls = [];
        $qb = $this->createQueryBuilderMock($andWhereCalls);

        $this->filter->apply(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            \stdClass::class,
            null,
            ['filters' => [EventBoundingBoxFilter::PROPERTY_NAME => ['ne' => ['lat' => '49.5', 'lng' => '3.25'], 'sw' => ['lat' => '48.0', 'lng' => '2.0']]]]
        );

        self::assertSame([], $andWhereCalls);
    }

    public function testGetDescriptionDescribesTheFourBoundingBoxCorners(): void
    {
        $description = $this->filter->getDescription(Event::class);

        self::assertSame(['bbox[ne][lat]', 'bbox[ne][lng]', 'bbox[sw][lat]', 'bbox[sw][lng]'], array_keys($description));
        self::assertSame('postAddress.latitude', $description['bbox[ne][lat]']['property']);
        self::assertSame('postAddress.longitude', $description['bbox[sw][lng]']['property']);
        self::assertFalse($description['bbox[ne][lat]']['required']);
    }

    public function testGetDescriptionForUnrelatedResourceClassReturnsEmpty(): void
    {
        self::assertSame([], $this->filter->getDescription(\stdClass::class));
    }

    private function applyFilter(QueryBuilder $queryBuilder, mixed $value): void
    {
        $this->filter->apply(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Event::class,
            null,
            ['filters' => [EventBoundingBoxFilter::PROPERTY_NAME => $value]]
        );
    }

    private function createQueryBuilderMock(array &$andWhereCalls = [], array &$setParameterCalls = []): QueryBuilder&MockObject
    {
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('getRootAliases')->willReturn(['e']);
        $qb->method('andWhere')->willReturnCallback(function (string $condition) use (&$andWhereCalls, $qb) {
            $andWhereCalls[] = $condition;

            return $qb;
        });
        $qb->method('setParameter')->willReturnCallback(function (string $key, mixed $paramValue) use (&$setParameterCalls, $qb) {
            $setParameterCalls[$key] = $paramValue;

            return $qb;
        });

        return $qb;
    }
}

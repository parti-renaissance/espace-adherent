<?php

namespace Tests\App\Algolia;

use Algolia\SearchBundle\SearchService;
use App\Algolia\AlgoliaIndexedEntityManager;
use App\Entity\AlgoliaIndexedEntityInterface;
use App\Entity\Timeline\Measure;
use App\Entity\Timeline\Theme;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AlgoliaIndexedEntityManagerTest extends TestCase
{
    public function testPostPersist()
    {
        $entity = $this->createMock(AlgoliaIndexedEntityInterface::class);

        $indexer = $this->createMock(SearchService::class);
        $indexer->expects($this->once())->method('index')->with(
            $this->isInstanceOf(EntityManagerInterface::class),
            $entity
        );

        $this->getManager($indexer)->postPersist($entity);
    }

    public function testPostPersistForMeasure()
    {
        $themes = [
            $this->createMock(Theme::class),
            $this->createMock(Theme::class),
        ];

        $entity = $this->createMock(Measure::class);
        $entity
            ->expects($this->once())
            ->method('getThemesToIndex')
            ->willReturn(new ArrayCollection($themes))
        ;

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $series = [
            [$entityManagerMock, $entity, []],
            [$entityManagerMock, $themes, []],
        ];

        $indexer = $this->createMock(SearchService::class);
        $indexer
            ->expects($this->exactly(2))
            ->method('index')
            ->willReturnCallback(function (...$args) use (&$series) {
                $expectedArgs = array_shift($series);
                $this->assertSame($expectedArgs, $args);
            })
        ;

        $this->getManager($indexer, $entityManagerMock)->postPersist($entity);
    }

    public function testPostUpdate()
    {
        $entity = $this->createMock(AlgoliaIndexedEntityInterface::class);

        $indexer = $this->createMock(SearchService::class);
        $indexer
            ->expects($this->once())
            ->method('index')
            ->with($this->isInstanceOf(EntityManagerInterface::class), $entity, [])
        ;

        $this->getManager($indexer)->postUpdate($entity);
    }

    public function testPostUpdateForMeasure()
    {
        $themes = [
            $this->createMock(Theme::class),
            $this->createMock(Theme::class),
        ];

        $entity = $this->createMock(Measure::class);
        $entity
            ->expects($this->once())
            ->method('getThemesToIndex')
            ->willReturn(new ArrayCollection($themes))
        ;

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $series = [
            [$entityManagerMock, $entity, []],
            [$entityManagerMock, $themes, []],
        ];

        $indexer = $this->createMock(SearchService::class);
        $indexer
            ->expects($this->exactly(2))
            ->method('index')
            ->willReturnCallback(function (...$args) use (&$series) {
                $expectedArgs = array_shift($series);
                $this->assertSame($expectedArgs, $args);
            })
        ;

        $this->getManager($indexer, $entityManagerMock)->postUpdate($entity);
    }

    public function testPreRemove()
    {
        $entity = $this->createMock(AlgoliaIndexedEntityInterface::class);

        $indexer = $this->createMock(SearchService::class);
        $indexer->expects($this->once())->method('remove')->with(
            $this->isInstanceOf(EntityManagerInterface::class),
            $entity
        );

        $this->getManager($indexer)->preRemove($entity);
    }

    public function testPreRemoveForMeasure()
    {
        $themes = [
            $this->createMock(Theme::class),
            $this->createMock(Theme::class),
        ];

        $entity = $this->createMock(Measure::class);
        $entity
            ->expects($this->once())
            ->method('getThemesToIndex')
            ->willReturn(new ArrayCollection($themes))
        ;

        $indexer = $this->createMock(SearchService::class);
        $indexer->expects($this->once())->method('remove')->with(
            $this->isInstanceOf(EntityManagerInterface::class),
            $entity
        );
        $indexer->expects($this->once())->method('index')->with(
            $this->isInstanceOf(EntityManagerInterface::class),
            $themes
        );

        $this->getManager($indexer)->preRemove($entity);
    }

    private function getManager(SearchService $indexer, ?EntityManagerInterface $manager = null): AlgoliaIndexedEntityManager
    {
        return new AlgoliaIndexedEntityManager($indexer, $manager ?? $this->createMock(EntityManagerInterface::class));
    }
}

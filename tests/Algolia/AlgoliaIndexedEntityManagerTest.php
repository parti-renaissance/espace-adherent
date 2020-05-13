<?php

namespace Tests\App\Algolia;

use App\Algolia\AlgoliaIndexedEntityManager;
use App\Algolia\ManualIndexerInterface;
use App\Entity\AlgoliaIndexedEntityInterface;
use App\Entity\Timeline\Measure;
use App\Entity\Timeline\Theme;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class AlgoliaIndexedEntityManagerTest extends TestCase
{
    public function testPostPersist()
    {
        $entity = $this->createMock(AlgoliaIndexedEntityInterface::class);

        $indexer = $this->createMock(ManualIndexerInterface::class);
        $indexer->expects($this->once())->method('index')->with($entity);

        $manager = new AlgoliaIndexedEntityManager($indexer);
        $manager->postPersist($entity);
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

        $indexer = $this->createMock(ManualIndexerInterface::class);
        $indexer
            ->expects($this->exactly(2))
            ->method('index')
            ->withConsecutive([$entity], [$themes])
        ;

        $manager = new AlgoliaIndexedEntityManager($indexer);
        $manager->postPersist($entity);
    }

    public function testPostUpdate()
    {
        $entity = $this->createMock(AlgoliaIndexedEntityInterface::class);

        $indexer = $this->createMock(ManualIndexerInterface::class);
        $indexer->expects($this->once())->method('index')->with($entity);

        $manager = new AlgoliaIndexedEntityManager($indexer);
        $manager->postUpdate($entity);
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

        $indexer = $this->createMock(ManualIndexerInterface::class);
        $indexer
            ->expects($this->exactly(2))
            ->method('index')
            ->withConsecutive([$entity], [$themes])
        ;

        $manager = new AlgoliaIndexedEntityManager($indexer);
        $manager->postUpdate($entity);
    }

    public function testPreRemove()
    {
        $entity = $this->createMock(AlgoliaIndexedEntityInterface::class);

        $indexer = $this->createMock(ManualIndexerInterface::class);
        $indexer->expects($this->once())->method('unIndex')->with($entity);

        $manager = new AlgoliaIndexedEntityManager($indexer);
        $manager->preRemove($entity);
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

        $indexer = $this->createMock(ManualIndexerInterface::class);
        $indexer->expects($this->once())->method('unIndex')->with($entity);
        $indexer->expects($this->once())->method('index')->with($themes);

        $manager = new AlgoliaIndexedEntityManager($indexer);
        $manager->preRemove($entity);
    }
}

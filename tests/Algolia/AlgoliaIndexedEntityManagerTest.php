<?php

declare(strict_types=1);

namespace Tests\App\Algolia;

use Algolia\SearchBundle\SearchService;
use App\Algolia\AlgoliaIndexedEntityManager;
use App\Entity\AlgoliaIndexedEntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AlgoliaIndexedEntityManagerTest extends TestCase
{
    public function testPostPersist(): void
    {
        $entity = $this->createStub(AlgoliaIndexedEntityInterface::class);

        $indexer = $this->createMock(SearchService::class);
        $indexer->expects($this->once())->method('index')->with(
            $this->isInstanceOf(EntityManagerInterface::class),
            [$entity]
        );

        $this->getManager($indexer)->postPersist($entity);
    }

    public function testPostUpdate(): void
    {
        $entity = $this->createStub(AlgoliaIndexedEntityInterface::class);

        $indexer = $this->createMock(SearchService::class);
        $indexer
            ->expects($this->once())
            ->method('index')
            ->with($this->isInstanceOf(EntityManagerInterface::class), [$entity])
        ;

        $this->getManager($indexer)->postUpdate($entity);
    }

    public function testPreRemove(): void
    {
        $entity = $this->createStub(AlgoliaIndexedEntityInterface::class);

        $indexer = $this->createMock(SearchService::class);
        $indexer->expects($this->once())->method('remove')->with(
            $this->isInstanceOf(EntityManagerInterface::class),
            $entity
        );

        $this->getManager($indexer)->preRemove($entity);
    }

    private function getManager(SearchService $indexer, ?EntityManagerInterface $manager = null): AlgoliaIndexedEntityManager
    {
        return new AlgoliaIndexedEntityManager($indexer, $manager ?? $this->createStub(EntityManagerInterface::class));
    }
}

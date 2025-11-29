<?php

declare(strict_types=1);

namespace Tests\App\Test\Algolia;

use Algolia\AlgoliaSearch\Response\NullResponse;
use Algolia\SearchBundle\SearchService;
use Doctrine\Persistence\ObjectManager;

class DummySearchService implements SearchService
{
    public $entitiesToIndex = [];
    public $entitiesToUnIndex = [];

    private $decorated;

    public function __construct(SearchService $decorated)
    {
        $this->decorated = $decorated;
    }

    public function index(ObjectManager $objectManager, $searchables, $requestOptions = []): NullResponse
    {
        foreach (\is_array($searchables) ? $searchables : [$searchables] as $object) {
            if (!isset($this->entitiesToIndex[$object::class])) {
                $this->entitiesToIndex[$object::class] = 0;
            }

            ++$this->entitiesToIndex[$object::class];
        }

        return new NullResponse();
    }

    public function remove(ObjectManager $objectManager, $searchables, $requestOptions = []): NullResponse
    {
        foreach (\is_array($searchables) ? $searchables : [$searchables] as $object) {
            if (!isset($this->entitiesToUnIndex[$object::class])) {
                $this->entitiesToUnIndex[$object::class] = 0;
            }

            ++$this->entitiesToUnIndex[$object::class];
        }

        return new NullResponse();
    }

    public function getConfiguration()
    {
        return $this->decorated->getConfiguration();
    }

    public function getEntitiesToIndex(): array
    {
        return $this->entitiesToIndex;
    }

    public function getEntitiesToUnIndex(): array
    {
        return $this->entitiesToUnIndex;
    }

    public function countForIndexByType(string $className): int
    {
        return $this->entitiesToIndex[$className] ?? 0;
    }

    public function countForUnIndexByType(string $className): int
    {
        return $this->entitiesToUnIndex[$className] ?? 0;
    }

    public function isSearchable($className): bool
    {
        return $this->decorated->isSearchable($className);
    }

    public function getSearchables()
    {
    }

    public function searchableAs($className)
    {
    }

    public function clear($className, $requestOptions = [])
    {
    }

    public function delete($className, $requestOptions = [])
    {
    }

    public function search(ObjectManager $objectManager, $className, $query = '', $requestOptions = []): array
    {
        return [];
    }

    public function rawSearch($className, $query = '', $requestOptions = []): array
    {
        return [
            'hits' => [],
            'nbHits' => 0,
            'page' => 1,
            'nbPages' => 0,
            'hitsPerPage' => 20,
        ];
    }

    public function count($className, $query = '', $requestOptions = [])
    {
    }
}

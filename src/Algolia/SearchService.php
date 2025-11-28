<?php

declare(strict_types=1);

namespace App\Algolia;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\SearchBundle\SearchService as BaseSearchService;
use Doctrine\Persistence\ObjectManager;

class SearchService implements BaseSearchService
{
    private $decorated;

    public function __construct(BaseSearchService $decorated, bool $debug = false)
    {
        $this->decorated = $decorated;

        if ($debug) {
            Algolia::getLogger()->enable();
        }
    }

    public function isSearchable($className)
    {
        return $this->decorated->isSearchable($className);
    }

    public function getSearchables()
    {
        return $this->decorated->getSearchables();
    }

    public function getConfiguration()
    {
        return $this->decorated->getConfiguration();
    }

    public function searchableAs($className)
    {
        return $this->decorated->searchableAs($className);
    }

    public function index(ObjectManager $objectManager, $searchables, $requestOptions = [])
    {
        return $this->decorated->index($objectManager, $searchables, $requestOptions);
    }

    public function remove(ObjectManager $objectManager, $searchables, $requestOptions = [])
    {
        return $this->decorated->remove($objectManager, $searchables, $requestOptions);
    }

    public function clear($className, $requestOptions = [])
    {
        return $this->decorated->clear($className, $requestOptions);
    }

    public function delete($className, $requestOptions = [])
    {
        return $this->decorated->delete($className, $requestOptions);
    }

    public function search(ObjectManager $objectManager, $className, $query = '', $requestOptions = [])
    {
        return $this->decorated->search($objectManager, $className, $query, $requestOptions);
    }

    public function rawSearch($className, $query = '', $requestOptions = [])
    {
        return $this->decorated->rawSearch($className, $query, $requestOptions);
    }

    public function count($className, $query = '', $requestOptions = [])
    {
        return $this->decorated->count($className, $query, $requestOptions);
    }
}

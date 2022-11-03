<?php

namespace App\Algolia\Sonata\Model;

use Algolia\SearchBundle\SearchService;
use App\Algolia\Query\QueryBuilder;
use App\Algolia\Sonata\ProxyQuery\ProxyQuery;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;

class ModelManager implements ModelManagerInterface
{
    private $algolia;
    private $queryBuilder;

    public function __construct(SearchService $algolia, QueryBuilder $queryBuilder)
    {
        $this->algolia = $algolia;
        $this->queryBuilder = $queryBuilder;
    }

    public function createQuery(string $class): ProxyQueryInterface
    {
        return new ProxyQuery($this->algolia, $this->queryBuilder, $class);
    }

    public function hasMetadata(string $class): bool
    {
        return false;
    }

    public function getPaginationParameters(DatagridInterface $datagrid, $page)
    {
        $values = $datagrid->getValues();

        $values['_page'] = $page;

        return ['filter' => $values];
    }

    public function create($object): void
    {
    }

    public function update($object): void
    {
    }

    public function delete($object): void
    {
    }

    public function findBy($class, array $criteria = []): array
    {
        return [];
    }

    public function findOneBy($class, array $criteria = []): ?object
    {
        return null;
    }

    public function find($class, $id): ?object
    {
        return null;
    }

    public function batchDelete($class, ProxyQueryInterface $queryProxy): void
    {
    }

    public function getParentFieldDescription($parentAssociationMapping, $class)
    {
    }

    public function getIdentifierValues($model): array
    {
        return [];
    }

    public function getIdentifierFieldNames($class): array
    {
        return [];
    }

    public function getNormalizedIdentifier($model): ?string
    {
        return null;
    }

    public function getUrlsafeIdentifier($model): ?string
    {
        return null;
    }

    public function getModelInstance($class)
    {
        return null;
    }

    public function getModelCollectionInstance($class)
    {
        return null;
    }

    public function collectionRemoveElement(&$collection, &$element)
    {
    }

    public function collectionAddElement(&$collection, &$element)
    {
    }

    public function collectionHasElement(&$collection, &$element)
    {
        return false;
    }

    public function collectionClear(&$collection)
    {
    }

    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid)
    {
        return [];
    }

    public function modelReverseTransform($class, array $array = [])
    {
        return null;
    }

    public function modelTransform($class, $instance)
    {
        return $instance;
    }

    public function executeQuery($query)
    {
        return $query->execute();
    }

    public function getDataSourceIterator(
        DatagridInterface $datagrid,
        array $fields,
        $firstResult = null,
        $maxResult = null
    ) {
        return null;
    }

    public function getExportFields($class): array
    {
        return [];
    }

    public function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx): void
    {
    }

    public function reverseTransform(object $object, array $array = []): void
    {
    }

    public function supportsQuery(object $query): bool
    {
        return false;
    }
}

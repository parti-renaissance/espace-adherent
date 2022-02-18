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

    public function createQuery($class, $alias = 'o')
    {
        return new ProxyQuery($this->algolia, $this->queryBuilder, $class);
    }

    public function getModelIdentifier($class)
    {
        return [];
    }

    public function getDefaultSortValues($class)
    {
        return [
            '_page' => 1,
            '_per_page' => 25,
        ];
    }

    public function getNewFieldDescriptionInstance($class, $name, array $options = [])
    {
        $fieldDescription = new FieldDescription();
        $fieldDescription->setName($name);
        $fieldDescription->setOptions($options);

        return $fieldDescription;
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

    public function create($object)
    {
    }

    public function update($object)
    {
    }

    public function delete($object)
    {
    }

    public function findBy($class, array $criteria = [])
    {
        return [];
    }

    public function findOneBy($class, array $criteria = [])
    {
    }

    public function find($class, $id)
    {
    }

    public function batchDelete($class, ProxyQueryInterface $queryProxy)
    {
    }

    public function getParentFieldDescription($parentAssociationMapping, $class)
    {
    }

    public function getIdentifierValues($model)
    {
        return [];
    }

    public function getIdentifierFieldNames($class)
    {
        return [];
    }

    public function getNormalizedIdentifier($model)
    {
        return null;
    }

    public function getUrlsafeIdentifier($model)
    {
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

    public function getExportFields($class)
    {
        return [];
    }

    public function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx)
    {
    }
}

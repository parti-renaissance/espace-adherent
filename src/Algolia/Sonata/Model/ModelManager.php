<?php

namespace App\Algolia\Sonata\Model;

use Algolia\SearchBundle\SearchService;
use App\Algolia\Query\QueryBuilder;
use App\Algolia\Sonata\ProxyQuery\ProxyQuery;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
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
    }

    public function getIdentifierFieldNames($class)
    {
    }

    public function getNormalizedIdentifier($model)
    {
    }

    public function getUrlsafeIdentifier($model)
    {
    }

    public function getModelInstance($class)
    {
    }

    public function getModelCollectionInstance($class)
    {
    }

    public function collectionRemoveElement(&$collection, &$element)
    {
    }

    public function collectionAddElement(&$collection, &$element)
    {
    }

    public function collectionHasElement(&$collection, &$element)
    {
    }

    public function collectionClear(&$collection)
    {
    }

    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid)
    {
    }

    public function modelReverseTransform($class, array $array = [])
    {
    }

    public function modelTransform($class, $instance)
    {
    }

    public function executeQuery($query)
    {
    }

    public function getDataSourceIterator(
        DatagridInterface $datagrid,
        array $fields,
        $firstResult = null,
        $maxResult = null
    ) {
    }

    public function getExportFields($class)
    {
    }

    public function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx)
    {
    }
}

<?php

namespace App\Admin;

use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Filter\FilterInterface;

abstract class DatagridDecorator implements DatagridInterface
{
    /**
     * @var DatagridInterface
     */
    protected $decorated;

    public function __construct(DatagridInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function getPager()
    {
        return $this->decorated->getPager();
    }

    public function getQuery()
    {
        return $this->decorated->getQuery();
    }

    public function getResults()
    {
        return $this->decorated->getResults();
    }

    public function buildPager()
    {
        return $this->decorated->buildPager();
    }

    public function addFilter(FilterInterface $filter)
    {
        return $this->decorated->addFilter($filter);
    }

    public function getFilters()
    {
        return $this->decorated->getFilters();
    }

    public function reorderFilters(array $keys)
    {
        return $this->decorated->reorderFilters($keys);
    }

    public function getValues()
    {
        return $this->decorated->getValues();
    }

    public function getColumns()
    {
        return $this->decorated->getColumns();
    }

    public function setValue($name, $operator, $value)
    {
        return $this->decorated->setValue($name, $operator, $value);
    }

    public function getForm()
    {
        return $this->decorated->getForm();
    }

    public function getFilter($name)
    {
        return $this->decorated->getFilter($name);
    }

    public function hasFilter($name)
    {
        return $this->decorated->hasFilter($name);
    }

    public function removeFilter($name)
    {
        return $this->decorated->removeFilter($name);
    }

    public function hasActiveFilters()
    {
        return $this->decorated->hasActiveFilters();
    }

    public function hasDisplayableFilters()
    {
        return $this->decorated->hasDisplayableFilters();
    }
}

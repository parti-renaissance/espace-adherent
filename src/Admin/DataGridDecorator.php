<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Filter\FilterInterface;

abstract class DataGridDecorator implements DatagridInterface
{
    /**
     * @var DatagridInterface
     */
    protected $decorated;

    public function __construct(DatagridInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function getPager()
    {
        return $this->decorated->getPager();
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->decorated->getQuery();
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        return $this->decorated->getResults();
    }

    public function buildPager()
    {
        return $this->decorated->buildPager();
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(FilterInterface $filter)
    {
        return $this->decorated->addFilter($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return $this->decorated->getFilters();
    }

    /**
     * {@inheritdoc}
     */
    public function reorderFilters(array $keys)
    {
        return $this->decorated->reorderFilters($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        return $this->decorated->getValues();
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return $this->decorated->getColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($name, $operator, $value)
    {
        return $this->decorated->setValue($name, $operator, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->decorated->getForm();
    }

    /**
     * {@inheritdoc}
     */
    public function getFilter($name)
    {
        return $this->decorated->getFilter($name);
    }

    /**
     * {@inheritdoc}
     */
    public function hasFilter($name)
    {
        return $this->decorated->hasFilter($name);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFilter($name)
    {
        return $this->decorated->removeFilter($name);
    }

    /**
     * {@inheritdoc}
     */
    public function hasActiveFilters()
    {
        return $this->decorated->hasActiveFilters();
    }

    /**
     * {@inheritdoc}
     */
    public function hasDisplayableFilters()
    {
        return $this->decorated->hasDisplayableFilters();
    }
}

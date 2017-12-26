<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Symfony\Component\Form\FormInterface;

trait DatagridDecoratorTrait
{
    /**
     * @var DatagridInterface
     */
    protected $decorated;

    public function setDecorated(DatagridInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @return PagerInterface
     */
    public function getPager()
    {
        return $this->decorated->getPager();
    }

    /**
     * @return ProxyQueryInterface
     */
    public function getQuery()
    {
        return $this->decorated->getQuery();
    }

    /**
     * @return array
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
     * @param FilterInterface $filter
     *
     * @return FilterInterface
     */
    public function addFilter(FilterInterface $filter)
    {
        return $this->decorated->addFilter($filter);
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->decorated->getFilters();
    }

    /**
     * Reorder filters.
     *
     * @param array $keys
     */
    public function reorderFilters(array $keys)
    {
        return $this->decorated->reorderFilters($keys);
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->decorated->getValues();
    }

    /**
     * @return FieldDescriptionCollection
     */
    public function getColumns()
    {
        return $this->decorated->getColumns();
    }

    /**
     * @param string $name
     * @param string $operator
     * @param mixed  $value
     */
    public function setValue($name, $operator, $value)
    {
        return $this->decorated->setValue($name, $operator, $value);
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->decorated->getForm();
    }

    /**
     * @param string $name
     *
     * @return FilterInterface
     */
    public function getFilter($name)
    {
        return $this->decorated->getFilter($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasFilter($name)
    {
        return $this->decorated->hasFilter($name);
    }

    /**
     * @param string $name
     */
    public function removeFilter($name)
    {
        return $this->decorated->removeFilter($name);
    }

    /**
     * @return bool
     */
    public function hasActiveFilters()
    {
        return $this->decorated->hasActiveFilters();
    }

    /**
     * @return bool
     */
    public function hasDisplayableFilters()
    {
        return $this->decorated->hasDisplayableFilters();
    }
}

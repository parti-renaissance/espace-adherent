<?php

namespace App\Algolia\Sonata\Pager;

use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\DatagridBundle\Pager\BasePager;

class Pager extends BasePager implements PagerInterface
{
    public function computeNbResult()
    {
        $countQuery = clone $this->getQuery();

        return $countQuery->execute()->getNbHits();
    }

    public function getResults()
    {
        return $this->getQuery()->execute();
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function init()
    {
        $this->resetIterator();

        $this->getQuery()->setMaxResults($this->getMaxPerPage());

        $this->setNbResults($this->computeNbResult());

        if (\count($this->getParameters()) > 0) {
            $this->getQuery()->setParameters($this->getParameters());
        }

        if (0 === $this->getPage() || 0 === $this->getMaxPerPage() || 0 === $this->getNbResults()) {
            $this->setLastPage(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

            $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

            $this->getQuery()->setFirstResult($offset);
            $this->getQuery()->setMaxResults($this->getMaxPerPage());
        }
    }
}

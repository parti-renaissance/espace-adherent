<?php

namespace App\Algolia\Sonata\Pager;

use Sonata\AdminBundle\Datagrid\Pager as BasePager;
use Sonata\AdminBundle\Datagrid\PagerInterface;

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
            $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

            $this->getQuery()->setFirstResult($this->getPage() - 1);
            $this->getQuery()->setMaxResults($this->getMaxPerPage());
        }
    }
}

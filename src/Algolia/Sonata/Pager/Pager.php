<?php

namespace App\Algolia\Sonata\Pager;

use Sonata\AdminBundle\Datagrid\Pager as BasePager;
use Sonata\AdminBundle\Datagrid\PagerInterface;

class Pager extends BasePager implements PagerInterface
{
    private ?int $resultsCount = null;

    public function computeNbResult()
    {
        $countQuery = clone $this->getQuery();

        return $countQuery->execute()->getNbHits();
    }

    public function getResults()
    {
        return $this->getQuery()->execute();
    }

    public function init(): void
    {
        $this->getQuery()->setMaxResults($this->getMaxPerPage());

        $this->resultsCount = $this->computeNbResult();

        if (0 === $this->getPage() || 0 === $this->getMaxPerPage() || 0 === $this->resultsCount) {
            $this->setLastPage(0);
        } else {
            $this->setLastPage(ceil($this->resultsCount / $this->getMaxPerPage()));

            $this->getQuery()->setFirstResult($this->getPage() - 1);
            $this->getQuery()->setMaxResults($this->getMaxPerPage());
        }
    }

    public function getCurrentPageResults(): iterable
    {
        return $this->getQuery()->execute();
    }

    public function countResults(): int
    {
        return $this->resultsCount;
    }
}

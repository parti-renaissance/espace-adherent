<?php

namespace App\Admin;

use App\Repository\IdeasWorkshop\IdeaRepository;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

class IdeaDatagrid extends DatagridDecorator
{
    private $repository;
    private $cachedResults;

    public function __construct(DatagridInterface $decorated, IdeaRepository $repository)
    {
        parent::__construct($decorated);

        $this->repository = $repository;
    }

    public function getResults()
    {
        if (!$this->cachedResults) {
            $results = $this->decorated->getResults();

            foreach ($results as $result) {
                $result->contributorsCount = $this->repository->countContributors($result)['count'];
            }

            $this->cachedResults = $results;
        }

        return $this->cachedResults;
    }
}

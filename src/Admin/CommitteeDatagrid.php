<?php

namespace App\Admin;

use App\Committee\CommitteeManager;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

class CommitteeDatagrid extends DatagridDecorator
{
    private $manager;
    private $cachedResults;

    public function __construct(DatagridInterface $decorated, CommitteeManager $manager)
    {
        parent::__construct($decorated);

        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        if (!$this->cachedResults) {
            $results = $this->decorated->getResults();

            foreach ($results as $result) {
                $result->hosts = $this->manager->getCommitteeHosts($result);
                $result->creator = $this->manager->getCommitteeCreator($result);
            }

            $this->cachedResults = $results;
        }

        return $this->cachedResults;
    }
}

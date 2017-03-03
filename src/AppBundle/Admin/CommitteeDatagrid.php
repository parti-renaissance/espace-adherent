<?php

namespace AppBundle\Admin;

use AppBundle\Committee\CommitteeManager;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

class CommitteeDatagrid implements DatagridInterface
{
    use DatagridDecoratorTrait;

    private $manager;
    private $cachedResults;

    public function __construct(DatagridInterface $decorated, CommitteeManager $manager)
    {
        $this->setDecorated($decorated);

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
            }

            $this->cachedResults = $results;
        }

        return $this->cachedResults;
    }
}

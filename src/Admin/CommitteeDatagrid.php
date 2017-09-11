<?php

namespace AppBundle\Admin;

use AppBundle\Committee\CommitteeManager;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

class CommitteeDatagrid implements DatagridInterface
{
    use DatagridDecoratorTrait;
    /**
     * @var CommitteeManager
     */
    private $_manager;
    /**
     * @var cachedResults
     */
    private $_cachedResults;

    public function __construct(DatagridInterface $decorated, CommitteeManager $manager)
    {
        $this->setDecorated($decorated);

        $this->_manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        if (!$this->_cachedResults) {
            $results = $this->decorated->getResults();

            foreach ($results as $result) {
                $result->hosts = $this->manager->getCommitteeHosts($result);
            }

            $this->_cachedResults = $results;
        }

        return $this->_cachedResults;
    }
}

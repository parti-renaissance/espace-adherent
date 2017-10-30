<?php

namespace AppBundle\Admin;

use AppBundle\Group\GroupManager;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

class GroupDatagrid implements DatagridInterface
{
    use DatagridDecoratorTrait;

    private $manager;
    private $cachedResults;

    public function __construct(DatagridInterface $decorated, GroupManager $manager)
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
                $result->administrators = $this->manager->getGroupAdministrators($result);
            }

            $this->cachedResults = $results;
        }

        return $this->cachedResults;
    }
}

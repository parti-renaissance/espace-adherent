<?php

namespace AppBundle\Admin;

use AppBundle\Committee\CommitteeManager;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

class IsAdherentDatagrid extends DatagridDecorator
{
    private $manager;

    public function __construct(DatagridInterface $decorated, IsAdherentManager $manager)
    {
        parent::__construct($decorated);

        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        static $results = null;

        if (null === $results) {
            $results = $this->decorated->getResults();

            foreach ($results as $result) {
                $result->isAdherent = $this->manager->isAdherent($result->getEmailAddress());
            }
        }

        return $results;
    }
}

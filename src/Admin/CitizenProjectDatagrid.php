<?php

namespace AppBundle\Admin;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\Entity\CitizenProject;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

class CitizenProjectDatagrid extends DatagridDecorator
{
    private $manager;
    private $cachedResults;

    public function __construct(DatagridInterface $decorated, CitizenProjectManager $manager)
    {
        parent::__construct($decorated);

        $this->manager = $manager;
    }

    public function getResults()
    {
        if (!$this->cachedResults) {
            /* @var CitizenProject[] $results */
            $results = $this->decorated->getResults();

            $this->manager->injectCitizenProjectAdministrators($results);
            $this->manager->injectCitizenProjectCreator($results);
            $this->manager->injectCitizenProjectNextAction($results);

            $this->cachedResults = $results;
        }

        return $this->cachedResults;
    }
}

<?php

namespace AppBundle\Admin;

use AppBundle\Repository\AdherentRepository;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

class IsAdherentDatagrid extends DatagridDecorator
{
    private $repository;

    public function __construct(DatagridInterface $decorated, AdherentRepository $repository)
    {
        parent::__construct($decorated);

        $this->repository = $repository;
    }

    public function getResults(): ?array
    {
        static $results = null;

        if (null === $results) {
            $results = $this->decorated->getResults();

            foreach ($results as $result) {
                $result->isAdherent = $this->repository->isAdherent($result->getEmailAddress());
            }
        }

        return $results;
    }
}

<?php

namespace AppBundle\Admin;

use AppBundle\Entity\WebHook\WebHook;
use AppBundle\Repository\WebHookRepository;
use AppBundle\WebHook\Event;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

class WebHookDatagrid extends DatagridDecorator
{
    private $cachedResults;
    private $repository;

    public function __construct(DatagridInterface $decorated, WebHookRepository $repository)
    {
        parent::__construct($decorated);
        $this->repository = $repository;
    }

    public function getResults()
    {
        if (!$this->cachedResults) {
            $results = $this->decorated->getResults();

            // Web hooks must be created automatically if they are not yet created
            $events = array_map(function (WebHook $webHook) {return $webHook->getEvent(); }, $results);
            $events = array_diff(Event::values(), $events);

            foreach ($events as $event) {
                if (!$webHook = $this->repository->findOneByEvent($event)) {
                    $webHook = new WebHook($event);

                    $this->repository->save($webHook);
                }

                $results[] = $webHook;
            }

            $this->cachedResults = $results;
        }

        return $this->cachedResults;
    }
}

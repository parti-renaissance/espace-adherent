<?php

namespace AppBundle\Admin;

use AppBundle\Entity\WebHook\WebHook;
use AppBundle\WebHook\Event;
use AppBundle\WebHook\WebHookManager;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

class WebHookDataGrid extends DataGridDecorator
{
    private $cachedResults;
    private $manager;

    public function __construct(DatagridInterface $decorated, WebHookManager $manager)
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

            // Web hooks must be created automatically if they are not yet created
            $events = array_map(function (WebHook $webHook) {return $webHook->getEvent(); }, $results);
            $events = array_diff(Event::values(), $events);

            foreach ($events as $event) {
                $results[] = $this->manager->getOrCreateWebHook($event);
            }

            $this->cachedResults = $results;
        }

        return $this->cachedResults;
    }
}

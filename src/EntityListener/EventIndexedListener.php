<?php

namespace App\EntityListener;

use App\Algolia\AlgoliaIndexedEntityManager;
use App\Entity\Event;

class EventIndexedListener
{
    private $manager;

    public function __construct(AlgoliaIndexedEntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function postPersist(Event $event): void
    {
        $this->manager->postPersist($event);
    }

    public function postUpdate(Event $event): void
    {
        $this->manager->postUpdate($event);
    }

    public function preRemove(Event $event): void
    {
        $this->manager->preRemove($event);
    }
}

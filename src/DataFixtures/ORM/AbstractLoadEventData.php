<?php

namespace App\DataFixtures\ORM;

use App\Event\EventFactory;
use App\Event\EventRegistrationFactory;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

abstract class AbstractLoadEventData extends Fixture
{
    private string $environment;
    protected EventFactory $eventFactory;
    protected EventRegistrationFactory $eventRegistrationFactory;

    abstract public function loadEvents(ObjectManager $manager): void;

    public function __construct(
        string $environment,
        EventFactory $eventFactory,
        EventRegistrationFactory $eventRegistrationFactory
    ) {
        $this->environment = $environment;
        $this->eventFactory = $eventFactory;
        $this->eventRegistrationFactory = $eventRegistrationFactory;
    }

    final public function load(ObjectManager $manager)
    {
        if ('test' === $this->environment) {
            Chronos::setTestNow($this->getDateTestNow());
        }

        $this->loadEvents($manager);

        if ('test' === $this->environment) {
            Chronos::setTestNow();
        }
    }

    protected function getDateTestNow(): string
    {
        return '2018-05-18';
    }
}

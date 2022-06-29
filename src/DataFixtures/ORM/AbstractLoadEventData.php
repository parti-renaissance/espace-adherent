<?php

namespace App\DataFixtures\ORM;

use App\Entity\PostAddress;
use App\Event\EventFactory;
use App\Event\EventRegistrationFactory;
use App\FranceCities\FranceCities;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

abstract class AbstractLoadEventData extends Fixture
{
    private string $environment;
    private FranceCities $franceCities;
    protected EventFactory $eventFactory;
    protected EventRegistrationFactory $eventRegistrationFactory;

    abstract public function loadEvents(ObjectManager $manager): void;

    public function __construct(
        string $environment,
        EventFactory $eventFactory,
        EventRegistrationFactory $eventRegistrationFactory,
        FranceCities $franceCities
    ) {
        $this->environment = $environment;
        $this->eventFactory = $eventFactory;
        $this->eventRegistrationFactory = $eventRegistrationFactory;
        $this->franceCities = $franceCities;
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

    protected function createPostAddress(
        string $street,
        string $cityCode,
        string $region = null,
        float $latitude = null,
        float $longitude = null
    ): PostAddress {
        [$postalCode, $inseeCode] = explode('-', $cityCode);
        $city = $this->franceCities->getCityByInseeCode($inseeCode);

        return PostAddress::createFrenchAddress($street, $cityCode, $city ? $city->getName() : null, $region, $latitude, $longitude);
    }
}

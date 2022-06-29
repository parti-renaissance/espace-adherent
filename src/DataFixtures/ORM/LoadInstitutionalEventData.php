<?php

namespace App\DataFixtures\ORM;

use App\Event\EventFactory;
use App\Event\EventRegistrationFactory;
use App\FranceCities\FranceCities;
use Cake\Chronos\Chronos;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadInstitutionalEventData extends AbstractLoadEventData implements DependentFixtureInterface
{
    public const INSTITUTIONAL_EVENT_1_UUID = '3f46976e-e76a-476e-86d7-575c6d3bc15e';

    public function __construct(
        string $environment,
        EventFactory $eventFactory,
        EventRegistrationFactory $eventRegistrationFactory,
        FranceCities $franceCities
    ) {
        parent::__construct($environment, $eventFactory, $eventRegistrationFactory, $franceCities);
    }

    public function loadEvents(ObjectManager $manager): void
    {
        $institutionalEvent1 = $this->eventFactory->createInstitutionalEventFromArray([
            'uuid' => self::INSTITUTIONAL_EVENT_1_UUID,
            'organizer' => $this->getReference('adherent-8'),
            'name' => 'Evénement institutionnel numéro 1',
            'category' => $this->getReference('institutional-event-category-1'),
            'description' => 'Un événement institutionnel',
            'address' => $this->createPostAddress('47 rue Martre', '92110-92024', null, 48.9015986, 2.3052684),
            'begin_at' => (new Chronos('+3 days'))->setTime(9, 30, 00, 000),
            'finish_at' => (new Chronos('+3 days'))->setTime(19, 00, 00, 000),
            'capacity' => 10,
            'time_zone' => 'Europe/Paris',
        ]);
        $institutionalEvent1->setPublished(true);
        $institutionalEvent1->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'));

        $manager->persist($institutionalEvent1);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadInstitutionalEventCategoryData::class,
            LoadReferentTagsZonesLinksData::class,
        ];
    }
}

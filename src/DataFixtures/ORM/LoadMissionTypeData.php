<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\MemberSummary\MissionType;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMissionTypeData implements FixtureInterface
{
    const MISSION_TYPES = [
        'MT_001' => 'Missions de bénévolat',
        'MT_002' => 'Mission locale',
        'MT_003' => 'Action publique',
        'MT_004' => 'Engagement',
        'MT_005' => 'Economie',
        'MT_006' => 'Emploi',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::MISSION_TYPES as $mission) {
            $missionType = new MissionType();
            $missionType->setName($mission);
            $manager->persist($missionType);
        }

        $manager->flush();
    }
}

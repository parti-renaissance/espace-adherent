<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Adherent\Activity\SourceTypeEnum;
use App\Entity\Adherent;
use App\Entity\Adherent\Activity\AdherentActivity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadAdherentActivityTestData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $carl = $this->getReference('adherent-2', Adherent::class);
        $adherent3 = $this->getReference('adherent-3', Adherent::class);

        $manager->createQuery('DELETE FROM App\Entity\Adherent\Activity\AdherentActivity aa WHERE aa.adherent IN (:adherents)')
            ->setParameter('adherents', [$carl, $adherent3])
            ->execute();

        $entries = [
            [
                'uuid' => 'b7b8e3a1-0001-0001-0001-000000000001',
                'sourceType' => SourceTypeEnum::Hit,
                'sourceId' => 9001,
                'eventType' => 'open',
                'occurredAt' => new \DateTime('2024-03-15 10:00:00'),
                'metadata' => [
                    'source' => 'page_events',
                    'object_type' => 'event',
                    'object_id' => 'abc123',
                    'button_name' => null,
                    'target_url' => null,
                ],
            ],
            [
                'uuid' => 'b7b8e3a1-0001-0001-0001-000000000002',
                'sourceType' => SourceTypeEnum::Hit,
                'sourceId' => 9002,
                'eventType' => 'activity_session',
                'occurredAt' => new \DateTime('2024-03-14 09:00:00'),
                'metadata' => [
                    'source' => null,
                    'object_type' => null,
                    'object_id' => null,
                    'button_name' => null,
                    'target_url' => null,
                ],
            ],
            [
                'uuid' => 'b7b8e3a1-0001-0001-0001-000000000003',
                'sourceType' => SourceTypeEnum::ActionHistory,
                'sourceId' => 9003,
                'eventType' => 'login_success',
                'occurredAt' => new \DateTime('2024-03-13 08:00:00'),
                'metadata' => null,
            ],
        ];

        foreach ($entries as $data) {
            $activity = new AdherentActivity();
            $activity->adherent = $carl;
            $activity->sourceType = $data['sourceType'];
            $activity->sourceId = $data['sourceId'];
            $activity->eventType = $data['eventType'];
            $activity->occurredAt = $data['occurredAt'];
            $activity->metadata = $data['metadata'];
            $activity->createdAt = new \DateTime('2024-03-13 07:00:00');

            $ref = new \ReflectionProperty($activity, 'uuid');
            $ref->setValue($activity, Uuid::fromString($data['uuid']));

            $manager->persist($activity);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [LoadAdherentActivityData::class];
    }
}

<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\InternalApiApplication;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadInternalApiApplicationData extends Fixture
{
    public const INTERNAL_API_APPLICATION_01_UUID = '50594332-7766-47de-b1f1-72d4328d8ec0';
    public const INTERNAL_API_APPLICATION_02_UUID = '3e50211c-ce07-487d-86ce-f5118acdbdad';
    public const INTERNAL_API_APPLICATION_03_UUID = 'b349ac42-a1e9-475f-92bc-c736fac974a7';
    public const INTERNAL_API_APPLICATION_04_UUID = 'd8203641-b7e2-4253-be75-eb3302f603b0';

    public function load(ObjectManager $manager): void
    {
        $data = [
            'application-1' => new InternalApiApplication(
                'data-corner',
                'http://enmarche.code',
                false,
                Uuid::fromString(self::INTERNAL_API_APPLICATION_01_UUID)
            ),
            'application-2' => new InternalApiApplication(
                'je-marche',
                'http://enmarche.code',
                false,
                Uuid::fromString(self::INTERNAL_API_APPLICATION_02_UUID)
            ),
            'application-3' => new InternalApiApplication(
                'app-test',
                'http://test.enmarche.code',
                false,
                Uuid::fromString(self::INTERNAL_API_APPLICATION_03_UUID)
            ),
            'application-4' => new InternalApiApplication(
                'app-test-scope',
                'http://test.enmarche.code',
                true,
                Uuid::fromString(self::INTERNAL_API_APPLICATION_04_UUID)
            ),
        ];

        foreach ($data as $application) {
            $manager->persist($application);
        }

        $manager->flush();
    }
}

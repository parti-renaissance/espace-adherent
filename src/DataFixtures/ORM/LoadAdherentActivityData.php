<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Adherent\Activity\PopulateAdherentActivityService;
use App\Adherent\Activity\SourceTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadAdherentActivityData extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly PopulateAdherentActivityService $service)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ([SourceTypeEnum::ActionHistory, SourceTypeEnum::Hit] as $sourceType) {
            $shouldContinue = true;
            while ($shouldContinue) {
                $shouldContinue = $this->service->processBatch($sourceType);
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            LoadUserActionHistoryData::class,
            LoadAppHitData::class,
        ];
    }
}

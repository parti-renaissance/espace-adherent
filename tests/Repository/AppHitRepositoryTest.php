<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Entity\Adherent;
use App\Entity\AppHit;
use App\JeMengage\Hit\EventTypeEnum;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Repository\AppHitRepository;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class AppHitRepositoryTest extends AbstractKernelTestCase
{
    private ?AppHitRepository $repository = null;

    public function testCountImpressionAndOpenStatsExcludesSuspiciousEmailClicks(): void
    {
        $objectUuid = Uuid::uuid4();

        // Get adherents from fixtures
        $adherent1 = $this->getAdherentRepository()->findOneByEmail('luciole1989@spambox.fr');
        $adherent2 = $this->getAdherentRepository()->findOneByEmail('gisele-berthoux@caramail.com');
        $adherent3 = $this->getAdherentRepository()->findOneByEmail('lolodie.dutemps@hotnix.tld');

        self::assertNotNull($adherent1, 'Fixture adherent 1 should exist');
        self::assertNotNull($adherent2, 'Fixture adherent 2 should exist');
        self::assertNotNull($adherent3, 'Fixture adherent 3 should exist');

        // 2 reliable email clicks
        $this->createAppHit($adherent1, EventTypeEnum::Click, 'email', $objectUuid->toString(), false);
        $this->createAppHit($adherent2, EventTypeEnum::Click, 'email', $objectUuid->toString(), false);
        // 1 suspicious email click (should be excluded)
        $this->createAppHit($adherent3, EventTypeEnum::Click, 'email', $objectUuid->toString(), true);
        // 2 email opens (not affected by suspicious filter)
        $this->createAppHit($adherent1, EventTypeEnum::Open, 'email', $objectUuid->toString(), false);
        $this->createAppHit($adherent2, EventTypeEnum::Open, 'email', $objectUuid->toString(), false);

        $this->manager->flush();

        $stats = $this->repository->countImpressionAndOpenStats(TargetTypeEnum::Publication, $objectUuid);

        self::assertSame(2, $stats['unique_clicks__email'], 'Should count only non-suspicious email clicks');
        self::assertSame(2, $stats['unique_opens__email'], 'Opens should not be affected by suspicious filter');
    }

    public function testCountImpressionAndOpenStatsAllSuspiciousClicksReturnsZero(): void
    {
        $objectUuid = Uuid::uuid4();

        $adherent1 = $this->getAdherentRepository()->findOneByEmail('luciole1989@spambox.fr');
        $adherent2 = $this->getAdherentRepository()->findOneByEmail('gisele-berthoux@caramail.com');

        // All email clicks are suspicious
        $this->createAppHit($adherent1, EventTypeEnum::Click, 'email', $objectUuid->toString(), true);
        $this->createAppHit($adherent2, EventTypeEnum::Click, 'email', $objectUuid->toString(), true);

        $this->manager->flush();

        $stats = $this->repository->countImpressionAndOpenStats(TargetTypeEnum::Publication, $objectUuid);

        self::assertSame(0, $stats['unique_clicks__email'], 'All suspicious clicks should be excluded');
    }

    private function createAppHit(
        Adherent $adherent,
        EventTypeEnum $eventType,
        string $source,
        string $objectId,
        bool $suspicious,
    ): AppHit {
        $appHit = new AppHit();
        $appHit->adherent = $adherent;
        $appHit->eventType = $eventType;
        $appHit->source = $source;
        $appHit->objectType = TargetTypeEnum::Publication;
        $appHit->objectId = $objectId;
        $appHit->appDate = new \DateTimeImmutable();
        $appHit->activitySessionUuid = Uuid::uuid4();
        $appHit->suspicious = $suspicious;

        $this->manager->persist($appHit);

        return $appHit;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get(AppHitRepository::class);
    }

    protected function tearDown(): void
    {
        $this->repository = null;

        parent::tearDown();
    }
}

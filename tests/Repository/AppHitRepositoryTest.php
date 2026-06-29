<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Entity\Adherent;
use App\Entity\AppHit;
use App\JeMengage\Hit\EventTypeEnum;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Repository\AppHitRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class AppHitRepositoryTest extends AbstractKernelTestCase
{
    private ?AppHitRepository $repository = null;

    public function testCountImpressionAndOpenStatsExcludesSuspiciousEmailClicks(): void
    {
        $objectUuid = Uuid::v4();

        // Get adherents from fixtures
        $adherent1 = $this->getAdherentRepository()->findOneByEmail('luciole1989@spambox.fr');
        $adherent2 = $this->getAdherentRepository()->findOneByEmail('gisele-berthoux@caramail.com');
        $adherent3 = $this->getAdherentRepository()->findOneByEmail('lolodie.dutemps@hotnix.tld');

        self::assertNotNull($adherent1, 'Fixture adherent 1 should exist');
        self::assertNotNull($adherent2, 'Fixture adherent 2 should exist');
        self::assertNotNull($adherent3, 'Fixture adherent 3 should exist');

        // 2 reliable email clicks
        $this->createAppHit($adherent1, EventTypeEnum::Click, 'email', $objectUuid->toRfc4122(), false);
        $this->createAppHit($adherent2, EventTypeEnum::Click, 'email', $objectUuid->toRfc4122(), false);
        // 1 suspicious email click (should be excluded)
        $this->createAppHit($adherent3, EventTypeEnum::Click, 'email', $objectUuid->toRfc4122(), true);
        // 2 email opens (not affected by suspicious filter)
        $this->createAppHit($adherent1, EventTypeEnum::Open, 'email', $objectUuid->toRfc4122(), false);
        $this->createAppHit($adherent2, EventTypeEnum::Open, 'email', $objectUuid->toRfc4122(), false);

        $this->manager->flush();

        $stats = $this->repository->countImpressionAndOpenStats(TargetTypeEnum::Publication, $objectUuid);

        self::assertSame(2, $stats['unique_clicks__email'], 'Should count only non-suspicious email clicks');
        self::assertSame(2, $stats['unique_opens__email'], 'Opens should not be affected by suspicious filter');
    }

    public function testCountImpressionAndOpenStatsAllSuspiciousClicksReturnsZero(): void
    {
        $objectUuid = Uuid::v4();

        $adherent1 = $this->getAdherentRepository()->findOneByEmail('luciole1989@spambox.fr');
        $adherent2 = $this->getAdherentRepository()->findOneByEmail('gisele-berthoux@caramail.com');

        // All email clicks are suspicious
        $this->createAppHit($adherent1, EventTypeEnum::Click, 'email', $objectUuid->toRfc4122(), true);
        $this->createAppHit($adherent2, EventTypeEnum::Click, 'email', $objectUuid->toRfc4122(), true);

        $this->manager->flush();

        $stats = $this->repository->countImpressionAndOpenStats(TargetTypeEnum::Publication, $objectUuid);

        self::assertSame(0, $stats['unique_clicks__email'], 'All suspicious clicks should be excluded');
    }

    public function testMarkSuspiciousEmailClicksFlagsOnlySameSecondBurstsPerAdherent(): void
    {
        $objectId = Uuid::v4()->toRfc4122();

        $adherent1 = $this->getAdherentRepository()->findOneByEmail('luciole1989@spambox.fr');
        $adherent2 = $this->getAdherentRepository()->findOneByEmail('gisele-berthoux@caramail.com');

        $sameSecond = new \DateTime('2026-06-23 10:00:00');
        $otherSecond = new \DateTime('2026-06-23 10:00:01');

        // adherent1: two clicks in the same second -> bot burst, both flagged.
        $this->createAppHit($adherent1, EventTypeEnum::Click, 'email', $objectId, false, $sameSecond);
        $this->createAppHit($adherent1, EventTypeEnum::Click, 'email', $objectId, false, $sameSecond);
        // adherent1: a lone click in another second -> legitimate.
        $this->createAppHit($adherent1, EventTypeEnum::Click, 'email', $objectId, false, $otherSecond);
        // adherent2: a single click in the same second as adherent1's burst -> legitimate (rule is per adherent).
        $this->createAppHit($adherent2, EventTypeEnum::Click, 'email', $objectId, false, $sameSecond);

        $this->manager->flush();

        $this->repository->markSuspiciousEmailClicks($objectId);

        $conn = $this->manager->getConnection();
        self::assertSame(2, (int) $conn->fetchOne(
            'SELECT COUNT(*) FROM app_hit WHERE object_id = ? AND suspicious = 1',
            [$objectId]
        ), 'Only the same-second burst is flagged');
        self::assertSame(2, (int) $conn->fetchOne(
            'SELECT COUNT(*) FROM app_hit WHERE object_id = ? AND adherent_id = ? AND suspicious = 1',
            [$objectId, $adherent1->getId()]
        ), 'Both clicks of the burst are flagged');
        self::assertSame(0, (int) $conn->fetchOne(
            'SELECT COUNT(*) FROM app_hit WHERE object_id = ? AND adherent_id = ? AND suspicious = 1',
            [$objectId, $adherent2->getId()]
        ), 'A single click is never flagged');
    }

    private function createAppHit(
        Adherent $adherent,
        EventTypeEnum $eventType,
        string $source,
        string $objectId,
        bool $suspicious,
        ?\DateTimeInterface $appDate = null,
    ): AppHit {
        $appHit = new AppHit();
        $appHit->adherent = $adherent;
        $appHit->eventType = $eventType;
        $appHit->source = $source;
        $appHit->objectType = TargetTypeEnum::Publication->value;
        $appHit->objectId = $objectId;
        $appHit->appDate = $appDate ?? new \DateTime();
        $appHit->activitySessionUuid = Uuid::v4();
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

<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\CandidateSelection;

use App\AdherentMessage\PublicationZone;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\JeMengage\Timeline\CandidateSelection\AudienceContextFactory;
use App\JeMengage\Timeline\Indexer\UserProfileFactory;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

/**
 * Functional on purpose: the deep zone hierarchy (city -> department/region/country parents), the
 * assembly tag and the committee membership are real wiring loaded from the fixtures, not stubs.
 * Fixture user: jacques.picard@en-marche.fr — tag "adherent:a_jour_2026:recotisation", direct zones
 * district 75-1 / city 75056 / borough 75108, assembly zone = department 75 (tagged "assembly").
 */
#[Group('functional')]
final class AudienceContextFactoryTest extends AbstractKernelTestCase
{
    private const string EMAIL = 'jacques.picard@en-marche.fr';
    private const string COMMITTEE_UUID = '515a56c0-bde8-56ef-b90c-4745b1c93818';

    private ?AudienceContextFactory $factory = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Until a phase-6+ service consumes it, the factory has no referent and is removed from the
        // compiled container: build it on top of the container-provided UserProfileFactory instead.
        $this->factory = new AudienceContextFactory(static::getContainer()->get(UserProfileFactory::class));
    }

    protected function tearDown(): void
    {
        $this->factory = null;

        parent::tearDown();
    }

    public function testCreateExpandsTagPrefixes(): void
    {
        $context = $this->factory->create($this->adherent());

        self::assertSame(
            ['adherent', 'adherent:a_jour_2026', 'adherent:a_jour_2026:recotisation'],
            $context->tagPrefixes
        );
    }

    public function testCreateReachZonesAreAssemblyAndDirectCitiesOnly(): void
    {
        $context = $this->factory->create($this->adherent());

        // Assembly zone first, then the direct city — deep parents (region, country) and direct
        // non-city zones (district, borough) must NOT leak into the reach set.
        self::assertSame(['department:75', 'city:75056'], $context->reachZones);
    }

    public function testCreateGroupsDeepZonesByPublicationZoneTypes(): void
    {
        $byType = $this->factory->create($this->adherent())->zoneCodesByType;

        self::assertSame(PublicationZone::ZONE_TYPES, array_keys($byType));
        self::assertSame(['75108'], $byType[Zone::BOROUGH]);
        self::assertSame(['75056'], $byType[Zone::CITY]);
        self::assertSame(['75'], $byType[Zone::DEPARTMENT]);
        self::assertSame(['11'], $byType[Zone::REGION]);
        self::assertSame(['FR'], $byType[Zone::COUNTRY]);
        self::assertSame(['75-1'], $byType[Zone::DISTRICT]);
        self::assertSame([], $byType[Zone::CANTON]);
        self::assertSame([], $byType[Zone::FOREIGN_DISTRICT]);
        self::assertSame([], $byType[Zone::CUSTOM]);
    }

    public function testCreateComposesUserProfile(): void
    {
        $adherent = $this->adherent();

        $profile = $this->factory->create($adherent)->profile;

        self::assertSame($adherent->getId(), $profile->userId);
        self::assertSame($adherent->tags, $profile->tags);
        self::assertContains('department:75', $profile->zones);
        self::assertSame([self::COMMITTEE_UUID], $profile->committees);
        self::assertSame(1, $profile->committeeMember);
        self::assertNotNull($profile->registeredDate);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $profile->registeredDate);
    }

    private function adherent(): Adherent
    {
        $adherent = $this->manager->getRepository(Adherent::class)->findOneBy(['emailAddress' => self::EMAIL]);
        self::assertNotNull($adherent);

        return $adherent;
    }
}

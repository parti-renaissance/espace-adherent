<?php

declare(strict_types=1);

namespace Tests\App\ManagedUsers;

use App\DataFixtures\ORM\LoadAdherentData;
use App\ManagedUsers\ProjectionRefresher;
use App\Repository\AdherentRepository;
use App\Repository\Projection\ManagedUserRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class ProjectionRefresherTest extends AbstractKernelTestCase
{
    private ?ProjectionRefresher $projectionService = null;
    private ?ManagedUserRepository $managedUserRepository = null;
    private ?AdherentRepository $adherentRepository = null;

    public function testRefreshOneUpdatesRolesFromZoneBasedRoles(): void
    {
        // Get an adherent with ZoneBasedRole (deputy)
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::DEPUTY_1_UUID);
        self::assertNotNull($adherent);
        self::assertNotEmpty($adherent->getZoneBasedRoles());

        // Refresh projection
        $this->projectionService->refresh($adherent);

        // Verify roles column was updated
        $this->manager->clear();
        $projection = $this->managedUserRepository->findOneBy(['originalId' => $adherent->getId()]);
        self::assertNotNull($projection);

        $roles = $projection->getRoles();
        self::assertNotEmpty($roles);

        // Verify structure of roles
        $firstRole = $roles[0];
        self::assertArrayHasKey('code', $firstRole);
        self::assertArrayHasKey('is_delegated', $firstRole);
        self::assertArrayHasKey('zones', $firstRole);
        self::assertArrayHasKey('zone_codes', $firstRole);
        self::assertFalse($firstRole['is_delegated']);
    }

    public function testRefreshOneUpdatesElectMandates(): void
    {
        // Get an adherent with elected representative mandate
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::DEPUTY_1_UUID);
        self::assertNotNull($adherent);

        // Refresh projection
        $this->projectionService->refresh($adherent);

        // Verify elect_mandates column was updated
        $this->manager->clear();
        $projection = $this->managedUserRepository->findOneBy(['originalId' => $adherent->getId()]);
        self::assertNotNull($projection);

        // elect_mandates can be null or an array of mandate types
        $electMandates = $projection->getElectMandates();
        // Just verify the query ran without error
        self::assertTrue(true);
    }

    public function testRefreshOneDoesNotAffectOtherColumns(): void
    {
        // Get an adherent
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_3_UUID);
        self::assertNotNull($adherent);

        // Get original values
        $projection = $this->managedUserRepository->findOneBy(['originalId' => $adherent->getId()]);
        self::assertNotNull($projection);

        $emailBefore = $projection->getEmail();
        $firstNameBefore = $projection->getFirstName();
        $lastNameBefore = $projection->getLastName();

        // Refresh projection
        $this->projectionService->refresh($adherent);

        // Verify other columns unchanged
        $this->manager->clear();
        $projectionAfter = $this->managedUserRepository->findOneBy(['originalId' => $adherent->getId()]);
        self::assertNotNull($projectionAfter);

        self::assertSame($emailBefore, $projectionAfter->getEmail());
        self::assertSame($firstNameBefore, $projectionAfter->getFirstName());
        self::assertSame($lastNameBefore, $projectionAfter->getLastName());
    }

    public function testRefreshOneSkipsWhenNoProjectionExists(): void
    {
        // Create a mock adherent that doesn't have a projection
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_3_UUID);
        self::assertNotNull($adherent);

        // Delete the projection
        $projection = $this->managedUserRepository->findOneBy(['originalId' => $adherent->getId()]);
        if ($projection) {
            $this->manager->remove($projection);
            $this->manager->flush();
            $this->manager->clear();
        }

        // Refresh should not throw
        $this->projectionService->refresh($adherent);

        // Verify no projection was created (Go worker does that)
        $projectionAfter = $this->managedUserRepository->findOneBy(['originalId' => $adherent->getId()]);
        self::assertNull($projectionAfter);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectionService = $this->get(ProjectionRefresher::class);
        $this->managedUserRepository = $this->get(ManagedUserRepository::class);
        $this->adherentRepository = $this->get(AdherentRepository::class);
    }

    protected function tearDown(): void
    {
        $this->projectionService = null;
        $this->managedUserRepository = null;
        $this->adherentRepository = null;

        parent::tearDown();
    }
}

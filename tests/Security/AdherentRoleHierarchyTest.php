<?php

declare(strict_types=1);

namespace Tests\App\Security;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Verifies the real role_hierarchy wiring (config/packages/security.php) for the membership levels:
 * ROLE_ADHERENT_A_JOUR > ROLE_ADHERENT > ROLE_MEMBRE > ROLE_USER.
 */
class AdherentRoleHierarchyTest extends KernelTestCase
{
    public function testAdherentAJourReachesAllLowerLevels(): void
    {
        $reachable = $this->reachableRoles('ROLE_ADHERENT_A_JOUR');

        self::assertContains('ROLE_ADHERENT', $reachable);
        self::assertContains('ROLE_MEMBRE', $reachable);
        self::assertContains('ROLE_USER', $reachable);
    }

    public function testAdherentReachesMembreAndUser(): void
    {
        $reachable = $this->reachableRoles('ROLE_ADHERENT');

        self::assertContains('ROLE_MEMBRE', $reachable);
        self::assertContains('ROLE_USER', $reachable);
    }

    public function testMembreReachesUserOnly(): void
    {
        $reachable = $this->reachableRoles('ROLE_MEMBRE');

        self::assertContains('ROLE_USER', $reachable);
        self::assertNotContains('ROLE_ADHERENT', $reachable);
    }

    /**
     * @return string[]
     */
    private function reachableRoles(string $role): array
    {
        self::bootKernel();

        /** @var RoleHierarchyInterface $hierarchy */
        $hierarchy = self::getContainer()->get('security.role_hierarchy');

        return $hierarchy->getReachableRoleNames([$role]);
    }
}

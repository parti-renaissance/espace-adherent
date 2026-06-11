<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Entity\AdherentZoneBasedRole;
use App\Scope\ScopeEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class AdherentRolesTest extends TestCase
{
    public function testContactEmitsRoleUserOnly(): void
    {
        $roles = $this->withTags([])->getRoles();

        self::assertContains('ROLE_USER', $roles);
        self::assertNotContains('ROLE_MEMBRE', $roles);
        self::assertNotContains('ROLE_ADHERENT', $roles);
        self::assertNotContains('ROLE_ADHERENT_A_JOUR', $roles);
    }

    public function testMembreEmitsRoleMembreNotRoleUser(): void
    {
        $roles = $this->withTags([TagEnum::SYMPATHISANT_MEMBRE])->getRoles();

        // Exactly one level role: ROLE_MEMBRE. ROLE_USER is obtained via role_hierarchy, not emitted.
        self::assertContains('ROLE_MEMBRE', $roles);
        self::assertNotContains('ROLE_USER', $roles);
        self::assertNotContains('ROLE_ADHERENT', $roles);
    }

    public function testAdherentEmitsRoleAdherentOnly(): void
    {
        $roles = $this->withTags([TagEnum::ADHERENT])->getRoles();

        // Single level role; cascade to ROLE_MEMBRE/ROLE_USER is applied by role_hierarchy, not getRoles().
        self::assertContains('ROLE_ADHERENT', $roles);
        self::assertNotContains('ROLE_USER', $roles);
        self::assertNotContains('ROLE_MEMBRE', $roles);
        self::assertNotContains('ROLE_ADHERENT_A_JOUR', $roles);
    }

    public function testActiveMembershipEmitsRoleAdherentAJourOnly(): void
    {
        $roles = $this->withTags([TagEnum::ADHERENT, TagEnum::getAdherentYearTag()])->getRoles();

        self::assertContains('ROLE_ADHERENT_A_JOUR', $roles);
        self::assertNotContains('ROLE_USER', $roles);
        self::assertNotContains('ROLE_ADHERENT', $roles);
    }

    public function testRolesAreUnique(): void
    {
        $adherent = $this->withTags([TagEnum::ADHERENT]);
        $roles = $adherent->getRoles();

        self::assertSame(array_values(array_unique($roles)), $roles);
    }

    public function testIsCadreFalseByDefault(): void
    {
        self::assertFalse(new Adherent()->isCadre());
    }

    public function testNationalRoleMakesCadre(): void
    {
        $adherent = new Adherent();
        $adherent->setNationalRole(true);

        self::assertTrue($adherent->isCadre());
    }

    public function testZoneBasedRoleMakesCadre(): void
    {
        $adherent = new Adherent();
        $adherent->addZoneBasedRole(new AdherentZoneBasedRole(ScopeEnum::DEPUTY));

        self::assertTrue($adherent->isCadre());
    }

    public function testIsEqualToIgnoresLevelRoleChange(): void
    {
        // Same identity, different membership-level roles → still equal (no session logout).
        $session = new Adherent();
        $session->addRoles(['ROLE_USER', 'ROLE_MEMBRE']);

        $fresh = $this->withTags([TagEnum::ADHERENT]);

        self::assertTrue($session->isEqualTo($fresh), 'A level change must not invalidate the session.');
    }

    public function testIsEqualToIgnoresFunctionalRoleChange(): void
    {
        // Same identity, different functional roles → still equal (cadre access is API/scope, not session).
        $session = new Adherent();

        $fresh = new Adherent();
        $fresh->addRoles(['ROLE_DEPUTY']);

        self::assertTrue($session->isEqualTo($fresh), 'A role change must not invalidate the session.');
    }

    public function testIsEqualToFalseForNonAdherent(): void
    {
        self::assertFalse(new Adherent()->isEqualTo($this->createStub(UserInterface::class)));
    }

    /**
     * @param string[] $tags
     */
    private function withTags(array $tags): Adherent
    {
        $adherent = new Adherent();
        $adherent->tags = $tags;

        return $adherent;
    }
}

<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\MyTeam\RoleEnum;
use App\Repository\AdherentRepository;
use App\Scope\ScopeEnum;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class AdherentRepositoryScopeTargetTest extends AbstractKernelTestCase
{
    private ?AdherentRepository $adherentRepository = null;

    public function testGetEmailsForScopeTargetsWithEmptyArray(): void
    {
        $emails = $this->adherentRepository->getEmailsForScopeTargets([]);

        $this->assertSame([], $emails);
    }

    public function testGetEmailsForScopeTargetsIncludeRoleOnly(): void
    {
        // Target: adherents with president_departmental_assembly role
        $scopeTargets = [
            [
                'role' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                'include_role' => true,
                'include_team' => false,
                'team_roles' => null,
            ],
        ];

        $emails = $this->adherentRepository->getEmailsForScopeTargets($scopeTargets);

        $this->assertNotEmpty($emails);
        // These emails should be found based on fixtures in LoadAdherentData.php
        // referent@en-marche-dev.fr and president-ad@renaissance-dev.fr have PAD role
        $this->assertContains('president-ad@renaissance-dev.fr', $emails);
    }

    public function testGetEmailsForScopeTargetsIncludeTeamOnly(): void
    {
        // Target: team members of president_departmental_assembly
        $scopeTargets = [
            [
                'role' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                'include_role' => false,
                'include_team' => true,
                'team_roles' => null,
            ],
        ];

        $emails = $this->adherentRepository->getEmailsForScopeTargets($scopeTargets);

        $this->assertNotEmpty($emails);
        // Based on LoadDelegatedAccessData.php, these users are delegated by PAD
        // deputy-75-1@en-marche-dev.fr, gisele-berthoux@caramail.com, jean-claude.dusse@example.fr
        $this->assertContains('gisele-berthoux@caramail.com', $emails);
    }

    public function testGetEmailsForScopeTargetsIncludeBothRoleAndTeam(): void
    {
        // Target: adherents with PAD role AND their team members
        $scopeTargets = [
            [
                'role' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                'include_role' => true,
                'include_team' => true,
                'team_roles' => null,
            ],
        ];

        $emails = $this->adherentRepository->getEmailsForScopeTargets($scopeTargets);

        $this->assertNotEmpty($emails);
        // Should contain both role holders and team members
        $this->assertContains('president-ad@renaissance-dev.fr', $emails);
        $this->assertContains('gisele-berthoux@caramail.com', $emails);
    }

    public function testGetEmailsForScopeTargetsWithTeamRolesFilter(): void
    {
        // Target: team members of deputy with specific role (mobilization_manager)
        $scopeTargets = [
            [
                'role' => ScopeEnum::DEPUTY,
                'include_role' => false,
                'include_team' => true,
                'team_roles' => [RoleEnum::MOBILIZATION_MANAGER],
            ],
        ];

        $emails = $this->adherentRepository->getEmailsForScopeTargets($scopeTargets);

        $this->assertNotEmpty($emails);
        // Based on LoadDelegatedAccessData.php fixtures, referent@en-marche-dev.fr
        // is delegated by deputy with mobilization_manager role
        $this->assertContains('referent@en-marche-dev.fr', $emails);
    }

    public function testGetEmailsForScopeTargetsWithCustomTeamRole(): void
    {
        // Target: team members of PAD with a custom role label
        $scopeTargets = [
            [
                'role' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                'include_role' => false,
                'include_team' => true,
                'team_roles' => ['Responsable communication'],  // Custom role label
            ],
        ];

        $emails = $this->adherentRepository->getEmailsForScopeTargets($scopeTargets);

        $this->assertNotEmpty($emails);
        // Based on LoadDelegatedAccessData.php, deputy@en-marche-dev.fr (deputy-75-1)
        // is delegated by PAD with custom role 'Responsable communication'
        $this->assertContains('deputy@en-marche-dev.fr', $emails);
    }

    public function testGetEmailsForScopeTargetsWithMixedStandardAndCustomRoles(): void
    {
        // Target: team members with both standard and custom roles
        $scopeTargets = [
            [
                'role' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                'include_role' => false,
                'include_team' => true,
                'team_roles' => [
                    RoleEnum::MOBILIZATION_MANAGER,      // Standard role
                    'Responsable communication',          // Custom role
                ],
            ],
        ];

        $emails = $this->adherentRepository->getEmailsForScopeTargets($scopeTargets);

        $this->assertNotEmpty($emails);
        // Should contain both standard role members and custom role members
        $this->assertContains('deputy@en-marche-dev.fr', $emails);  // Custom role
    }

    public function testGetEmailsForScopeTargetsWithInvalidRole(): void
    {
        $scopeTargets = [
            [
                'role' => 'invalid_role_that_does_not_exist',
                'include_role' => true,
                'include_team' => false,
                'team_roles' => null,
            ],
        ];

        $emails = $this->adherentRepository->getEmailsForScopeTargets($scopeTargets);

        // Invalid role should not match any zone-based role
        $this->assertSame([], $emails);
    }

    public function testGetEmailsForScopeTargetsWithMultipleRoles(): void
    {
        // Target: multiple scope targets
        $scopeTargets = [
            [
                'role' => ScopeEnum::DEPUTY,
                'include_role' => true,
                'include_team' => false,
                'team_roles' => null,
            ],
            [
                'role' => ScopeEnum::SENATOR,
                'include_role' => true,
                'include_team' => false,
                'team_roles' => null,
            ],
        ];

        $emails = $this->adherentRepository->getEmailsForScopeTargets($scopeTargets);

        $this->assertNotEmpty($emails);
        // Should contain deputies and senators from fixtures
        $this->assertContains('deputy@en-marche-dev.fr', $emails);
        $this->assertContains('senateur@en-marche-dev.fr', $emails);
    }

    public function testCountAdherentsForMessageWithScopeTargets(): void
    {
        // Create a message with filter containing scopeTargets
        $author = $this->adherentRepository->findOneByEmail('president-ad@renaissance-dev.fr');
        $this->assertInstanceOf(Adherent::class, $author);

        $message = AdherentMessage::createFromAdherent($author);
        $message->setInstanceScope(ScopeEnum::NATIONAL);
        $filter = new AdherentMessageFilter();
        $filter->setMessage($message);
        $filter->scopeTargets = [
            [
                'role' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                'include_role' => true,
                'include_team' => false,
                'team_roles' => null,
            ],
        ];
        $message->setFilter($filter);

        $count = $this->adherentRepository->countAdherentsForMessage($message, true);

        $this->assertGreaterThan(0, $count);
    }

    public function testCountAdherentsForMessageWithEmptyScopeTargetsResult(): void
    {
        // Create a message with filter containing scopeTargets that yields no results
        $author = $this->adherentRepository->findOneByEmail('president-ad@renaissance-dev.fr');
        $this->assertInstanceOf(Adherent::class, $author);

        $message = AdherentMessage::createFromAdherent($author);
        $message->setInstanceScope(ScopeEnum::NATIONAL);
        $filter = new AdherentMessageFilter();
        $filter->setMessage($message);
        $filter->scopeTargets = [
            [
                'role' => 'non_existent_role',
                'include_role' => true,
                'include_team' => false,
                'team_roles' => null,
            ],
        ];
        $message->setFilter($filter);

        $count = $this->adherentRepository->countAdherentsForMessage($message, true);

        $this->assertSame(0, $count);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;

        parent::tearDown();
    }
}

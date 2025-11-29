<?php

declare(strict_types=1);

namespace Tests\App\Entity;

use App\Entity\Administrator;
use App\Entity\AdministratorRole;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\App\AbstractKernelTestCase;

class AdministratorTest extends AbstractKernelTestCase
{
    #[DataProvider('provideAdminRoles')]
    public function testGetRoles(string $email, array $roleCodes): void
    {
        $administrator = $this->createAdministrator($email);

        foreach ($roleCodes as $roleCode) {
            $administrator->addAdministratorRole(
                $this->createAdministratorRole($roleCode)
            );
        }

        $roles = $administrator->getRoles();

        $this->assertIsArray($roles);

        $expectedRoles = array_merge(['ROLE_ADMIN_DASHBOARD'], $roleCodes);

        $this->assertCount(\count($expectedRoles), $roles);

        foreach ($expectedRoles as $expectedRole) {
            $this->assertContains($expectedRole, $roles);
        }
    }

    public static function provideAdminRoles(): iterable
    {
        yield ['test@admin.code', ['ROLE_SUPER_ADMIN']];
        yield ['test2@admin.code', ['ROLE_TEST_1', 'ROLE_TEST_2']];
        yield ['test3@admin.code', ['ROLE_TEST_1', 'ROLE_TEST_3', 'ROLE_TEST_4']];
    }

    public function testGetOnlyEnabledRoles(): void
    {
        $administrator = $this->createAdministrator('test@admin.code');

        $administrator->addAdministratorRole(
            $this->createAdministratorRole('ROLE_TEST_1', true)
        );
        $administrator->addAdministratorRole(
            $this->createAdministratorRole('ROLE_TEST_2', false)
        );

        $roles = $administrator->getRoles();

        $this->assertContains('ROLE_TEST_1', $roles);
        $this->assertNotContains('ROLE_TEST_2', $roles);
    }

    private function createAdministrator(string $email): Administrator
    {
        $administrator = new Administrator();

        $administrator->setEmailAddress($email);

        return $administrator;
    }

    private function createAdministratorRole(string $code, bool $enabled = true): AdministratorRole
    {
        $role = $this->createMock(AdministratorRole::class);

        $role->code = $code;
        $role->enabled = $enabled;

        return $role;
    }
}

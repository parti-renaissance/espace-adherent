<?php

namespace App\DataFixtures\ORM;

use App\Admin\AdministratorFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadAdminData extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly AdministratorFactory $administratorFactory)
    {
    }

    public function load(ObjectManager $manager)
    {
        $adminRoles = $writerRoles = $this->getRoles([
            'ROLE_SUPER_ADMIN',
        ]);

        $superAdmin2fa = $this->administratorFactory->createFromArray([
            'email' => 'titouan.galopin@en-marche.fr',
            'password' => 'secret!12345',
            'roles' => $adminRoles,
            'secret' => 'D3GU3BR4LUDK5NWR',
        ]);

        $admin = $this->administratorFactory->createFromArray([
            'email' => 'jean.dupond@en-marche.fr',
            'password' => 'secret!12345',
            'roles' => $adminRoles,
            'activated' => false,
        ]);
        $this->setReference('administrator-1', $admin);

        $writer = $this->administratorFactory->createFromArray([
            'email' => 'martin.pierre@en-marche.fr',
            'password' => 'secret!12345',
            'roles' => $writerRoles,
        ]);

        $manager->persist($this->administratorFactory->createFromArray([
            'email' => 'admin@en-marche-dev.fr',
            'password' => 'admin',
            'roles' => $adminRoles,
        ]));

        $manager->persist($superadmin = $this->administratorFactory->createFromArray([
            'email' => 'superadmin@en-marche-dev.fr',
            'password' => 'superadmin',
            'roles' => $this->getRoles(['ROLE_SUPER_ADMIN']),
        ]));
        $this->setReference('administrator-2', $superadmin);

        $manager->persist($this->administratorFactory->createFromArray([
            'email' => 'writer@en-marche-dev.fr',
            'password' => 'writer',
            'roles' => $writerRoles,
        ]));

        $manager->persist($renaissanceAdmin = $this->administratorFactory->createFromArray([
            'email' => 'admin@renaissance.code',
            'password' => 'renaissance',
            'roles' => $this->getRoles([
                'ROLE_ADMIN_RENAISSANCE_COMMITMENTS',
                'ROLE_ADMIN_RENAISSANCE_ARTICLES',
                'ROLE_ADMIN_RENAISSANCE_HOME_BLOCKS',
                'ROLE_ADMIN_RENAISSANCE_BIOGRAPHY_EXECUTIVE_OFFICE_MEMBERS',
                'ROLE_ADMIN_RENAISSANCE_ADHERENT_FORMATIONS',
                'ROLE_ADMIN_RENAISSANCE_DEPARTMENT_SITES',
            ]),
        ]));
        $this->setReference('administrator-renaissance', $renaissanceAdmin);

        $manager->persist($superAdmin2fa);
        $manager->persist($admin);
        $manager->persist($writer);
        $manager->flush();
    }

    private function getRoles(array $roleCodes): array
    {
        $roles = [];

        foreach ($roleCodes as $roleCode) {
            $roles[] = $this->getReference("administrator-role-$roleCode");
        }

        return $roles;
    }

    public function getDependencies()
    {
        return [
            LoadAdministratorRoleData::class,
        ];
    }
}

<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Admin\AdministratorFactory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadAdminData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $factory = $this->getAdministratorFactory();

        $adminRoles = [
            'ROLE_ADMIN_DASHBOARD',
            'ROLE_ADMIN_MEDIAS',
            'ROLE_ADMIN_CONTENT',
            'ROLE_ADMIN_HOME',
            'ROLE_ADMIN_PROPOSALS',
            'ROLE_ADMIN_FACEBOOK_PROFILES',
            'ROLE_ADMIN_REDIRECTIONS',
            'ROLE_ADMIN_NEWSLETTER',
            'ROLE_ADMIN_JE_MARCHE',
            'ROLE_ADMIN_TON_MACRON',
            'ROLE_ADMIN_LEGISLATIVES',
            'ROLE_ADMIN_ADHERENTS',
            'ROLE_ADMIN_SUMMARY',
            'ROLE_ADMIN_COMMITTEES',
            'ROLE_ADMIN_EVENTS',
            'ROLE_ADMIN_PROCURATIONS',
            'ROLE_ADMIN_DONATIONS',
            'ROLE_ADMIN_MAILJET',
        ];

        $writerRoles = [
            'ROLE_ADMIN_DASHBOARD',
            'ROLE_ADMIN_MEDIAS',
            'ROLE_ADMIN_CONTENT',
            'ROLE_ADMIN_HOME',
            'ROLE_ADMIN_PROPOSALS',
            'ROLE_ADMIN_FACEBOOK_PROFILES',
            'ROLE_ADMIN_REDIRECTIONS',
        ];

        $superAdmin = $factory->createFromArray([
            'email' => 'titouan.galopin@en-marche.fr',
            'password' => 'secret!12345',
            'roles' => $adminRoles,
        ]);

        $admin = $factory->createFromArray([
            'email' => 'jean.dupond@en-marche.fr',
            'password' => 'secret!12345',
            'roles' => $adminRoles,
        ]);

        $writer = $factory->createFromArray([
            'email' => 'martin.pierre@en-marche.fr',
            'password' => 'secret!12345',
            'roles' => $writerRoles,
        ]);

        $manager->persist($factory->createFromArray([
            'email' => 'admin@en-marche-dev.fr',
            'password' => 'admin',
            'roles' => $adminRoles,
        ]));

        $manager->persist($factory->createFromArray([
            'email' => 'superadmin@en-marche-dev.fr',
            'password' => 'superadmin',
            'roles' => ['ROLE_SUPER_ADMIN'],
        ]));

        $manager->persist($factory->createFromArray([
            'email' => 'writer@en-marche-dev.fr',
            'password' => 'writer',
            'roles' => $writerRoles,
        ]));

        $manager->persist($superAdmin);
        $manager->persist($admin);
        $manager->persist($writer);
        $manager->flush();
    }

    private function getAdministratorFactory(): AdministratorFactory
    {
        return $this->container->get('app.admin.administrator_factory');
    }
}

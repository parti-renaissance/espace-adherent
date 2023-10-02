<?php

namespace App\DataFixtures\ORM;

use App\Entity\AdministratorRole;
use App\Entity\AdministratorRoleGroupEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class LoadAdministratorRoleData extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->getRoles() as $administratorRole) {
            $manager->persist($administratorRole);

            $this->setReference(sprintf('administrator-role-%s', $administratorRole->code), $administratorRole);
        }

        $manager->flush();
    }

    private function createRole(
        string $code,
        string $label,
        AdministratorRoleGroupEnum $group,
        string $description = null,
        bool $enabled = true
    ): AdministratorRole {
        $role = new AdministratorRole();

        $role->code = $code;
        $role->label = $label;
        $role->enabled = $enabled;
        $role->groupCode = $group;
        $role->description = $description ?? $this->faker->text('200');

        return $role;
    }

    private function getRoles(): \Generator
    {
        // Tech
        yield $this->createRole(
            'ROLE_SUPER_ADMIN',
            'Super Administrateur',
            AdministratorRoleGroupEnum::TECH,
        );
        // Renaissance
        yield $this->createRole(
            'ROLE_ADMIN_RENAISSANCE_COMMITMENTS',
            'Administrateur des valeurs et engagements',
            AdministratorRoleGroupEnum::RE_SITE_WEB,
        );
        yield $this->createRole(
            'ROLE_ADMIN_RENAISSANCE_ARTICLES',
            'Administrateur des actualités',
            AdministratorRoleGroupEnum::RE_SITE_WEB,
        );
        yield $this->createRole(
            'ROLE_ADMIN_RENAISSANCE_HOME_BLOCKS',
            'Administrateur des blocs d\'articles',
            AdministratorRoleGroupEnum::RE_SITE_WEB,
        );
        yield $this->createRole(
            'ROLE_ADMIN_RENAISSANCE_BIOGRAPHY_EXECUTIVE_OFFICE_MEMBERS',
            'Administrateur des biographies du BurEx',
            AdministratorRoleGroupEnum::RE_SITE_WEB,
        );
        yield $this->createRole(
            'ROLE_ADMIN_RENAISSANCE_ADHERENT_FORMATIONS',
            'Administrateur des formations adhérent',
            AdministratorRoleGroupEnum::RE_SITE_WEB,
        );
        yield $this->createRole(
            'ROLE_ADMIN_RENAISSANCE_DEPARTMENT_SITES',
            'Administrateur des sites départementaux',
            AdministratorRoleGroupEnum::RE_SITE_WEB,
        );
        // Communication
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_MEDIAS',
            'Administrateur des médias (Upload)',
            AdministratorRoleGroupEnum::COMMUNICATION,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_ARTICLES',
            'Administrateur des articles',
            AdministratorRoleGroupEnum::COMMUNICATION,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_ARTICLE_CATEGORIES',
            'Administrateur des catégories d\'articles',
            AdministratorRoleGroupEnum::ARCHIVES,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_PAGES',
            'Administrateur des pages statiques',
            AdministratorRoleGroupEnum::COMMUNICATION,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_HOME_BLOCKS',
            'Administrateur des blocs d\'articles',
            AdministratorRoleGroupEnum::COMMUNICATION,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_CMS_BLOCKS',
            'Administrateur des blocs statiques',
            AdministratorRoleGroupEnum::COMMUNICATION,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_CLARIFICATIONS',
            'Administrateur des désintoxs',
            AdministratorRoleGroupEnum::COMMUNICATION,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_REDIRECTIONS',
            'Administrateur des redirections',
            AdministratorRoleGroupEnum::COMMUNICATION,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_BIOGRAPHY_EXEXECUTIVE_OFFICE_MEMBERS',
            'Administrateur des biographies BurEx',
            AdministratorRoleGroupEnum::COMMUNICATION,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_LIVE_LINKS',
            'Administrateur des liens en direct',
            AdministratorRoleGroupEnum::COMMUNICATION,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_NEWSLETTER_SUBSCRIPTIONS',
            'Administrateur des inscriptions aux newsletters',
            AdministratorRoleGroupEnum::COMMUNICATION,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_SMS_CAMPAIGNS',
            'Administrateur des campagnes SMS',
            AdministratorRoleGroupEnum::COMMUNICATION,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_QR_CODES',
            'Administrateur des QR Codes',
            AdministratorRoleGroupEnum::COMMUNICATION,
        );
        // Adhérents
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_ADHERENTS',
            'Administrateur des adhérents',
            AdministratorRoleGroupEnum::ADHERENTS,
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_IMPERSONATE',
            'Impersonnification des adhérents',
            AdministratorRoleGroupEnum::ADHERENTS,
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_BAN',
            'Bannissement des adhérents',
            AdministratorRoleGroupEnum::ADHERENTS,
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_UNREGISTER',
            'Désadhésion des adhérents',
            AdministratorRoleGroupEnum::ADHERENTS,
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_CERTIFICATIONS',
            'Administrateur des certifications adhérents',
            AdministratorRoleGroupEnum::ADHERENTS,
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_CERTIFICATION_HISTORIES',
            'Administrateur de l\'historique des certifications adhérents',
            AdministratorRoleGroupEnum::ADHERENTS,
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_INVITATIONS',
            'Administrateur des invitations adhérents',
            AdministratorRoleGroupEnum::ADHERENTS,
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_UNREGISTRATIONS',
            'Administrateur des desadhésions',
            AdministratorRoleGroupEnum::ADHERENTS,
        );
        // Politique
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_ADHERENT_ELECTED_REPRESENTATIVES',
            'Administrateur des adhérents élus',
            AdministratorRoleGroupEnum::POLITIQUE,
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_TECHNICAL_SKILLS',
            'Administrateur des compétences techniques (Muni)',
            AdministratorRoleGroupEnum::POLITIQUE,
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_THEMES',
            'Administrateur des thèmes (Muni)',
            AdministratorRoleGroupEnum::POLITIQUE,
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_TAGS',
            'Administrateur des tags de candidatures (Muni)',
            AdministratorRoleGroupEnum::POLITIQUE,
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_RUNNING_MATE_REQUESTS',
            'Administrateur des candidatures colistiers',
            AdministratorRoleGroupEnum::POLITIQUE,
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_VOLUNTEER_REQUESTS',
            'Administrateur des candidatures bénévoles',
            AdministratorRoleGroupEnum::POLITIQUE,
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_REPUBLICAN_SILENCES',
            'Administrateur des silences républicains',
            AdministratorRoleGroupEnum::POLITIQUE,
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_LEGISLATIVE_CANDIDATES',
            'Administrateur des candidats aux législatives',
            AdministratorRoleGroupEnum::POLITIQUE,
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_LEGISLATIVE_DISTRICT_ZONES',
            'Administrateur des zones de candidats aux législatives',
            AdministratorRoleGroupEnum::POLITIQUE,
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_ELECTED_REPRESENTATIVES',
            'Administrateur du registre national des élus (legacy)',
            AdministratorRoleGroupEnum::POLITIQUE,
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_ELECTION_CITY_CARDS',
            'Administrateur des villes (élections)',
            AdministratorRoleGroupEnum::POLITIQUE,
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_ELECTION_CITY_CARD_MANAGERS',
            'Administrateur du suivi/pilotage (élections)',
            AdministratorRoleGroupEnum::POLITIQUE,
        );
    }
}

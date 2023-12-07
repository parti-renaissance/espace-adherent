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
            AdministratorRoleGroupEnum::TECH
        );
        // Renaissance
        yield $this->createRole(
            'ROLE_ADMIN_RENAISSANCE_COMMITMENTS',
            'Administrateur des valeurs et engagements',
            AdministratorRoleGroupEnum::RE_SITE_WEB
        );
        yield $this->createRole(
            'ROLE_ADMIN_RENAISSANCE_ARTICLES',
            'Administrateur des actualités',
            AdministratorRoleGroupEnum::RE_SITE_WEB
        );
        yield $this->createRole(
            'ROLE_ADMIN_RENAISSANCE_HOME_BLOCKS',
            'Administrateur des blocs d\'articles',
            AdministratorRoleGroupEnum::RE_SITE_WEB
        );
        yield $this->createRole(
            'ROLE_ADMIN_RENAISSANCE_BIOGRAPHY_EXECUTIVE_OFFICE_MEMBERS',
            'Administrateur des biographies du BurEx',
            AdministratorRoleGroupEnum::RE_SITE_WEB
        );
        yield $this->createRole(
            'ROLE_ADMIN_RENAISSANCE_ADHERENT_FORMATIONS',
            'Administrateur des formations adhérent',
            AdministratorRoleGroupEnum::RE_SITE_WEB
        );
        yield $this->createRole(
            'ROLE_ADMIN_RENAISSANCE_DEPARTMENT_SITES',
            'Administrateur des sites départementaux',
            AdministratorRoleGroupEnum::RE_SITE_WEB
        );
        yield $this->createRole(
            'ROLE_ADMIN_RENAISSANCE_CREATE_ADHERENT',
            'Création/Vérification de compte adhérent RE',
            AdministratorRoleGroupEnum::RE_SITE_WEB
        );
        // Communication
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_MEDIAS',
            'Administrateur des médias (Upload)',
            AdministratorRoleGroupEnum::COMMUNICATION
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_ARTICLES',
            'Administrateur des articles',
            AdministratorRoleGroupEnum::COMMUNICATION
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_ARTICLE_CATEGORIES',
            'Administrateur des catégories d\'articles',
            AdministratorRoleGroupEnum::ARCHIVES,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_PAGES',
            'Administrateur des pages statiques',
            AdministratorRoleGroupEnum::COMMUNICATION
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_HOME_BLOCKS',
            'Administrateur des blocs d\'articles',
            AdministratorRoleGroupEnum::COMMUNICATION
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_CMS_BLOCKS',
            'Administrateur des blocs statiques',
            AdministratorRoleGroupEnum::COMMUNICATION
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_CLARIFICATIONS',
            'Administrateur des désintoxs',
            AdministratorRoleGroupEnum::COMMUNICATION
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_REDIRECTIONS',
            'Administrateur des redirections',
            AdministratorRoleGroupEnum::COMMUNICATION
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_BIOGRAPHY_EXEXECUTIVE_OFFICE_MEMBERS',
            'Administrateur des biographies BurEx',
            AdministratorRoleGroupEnum::COMMUNICATION
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_LIVE_LINKS',
            'Administrateur des liens en direct',
            AdministratorRoleGroupEnum::COMMUNICATION
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_NEWSLETTER_SUBSCRIPTIONS',
            'Administrateur des inscriptions aux newsletters',
            AdministratorRoleGroupEnum::COMMUNICATION
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_SMS_CAMPAIGNS',
            'Administrateur des campagnes SMS',
            AdministratorRoleGroupEnum::COMMUNICATION,
        );
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNICATION_QR_CODES',
            'Administrateur des QR Codes',
            AdministratorRoleGroupEnum::COMMUNICATION
        );
        // Adhérents
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_ADHERENTS',
            'Administrateur des adhérents',
            AdministratorRoleGroupEnum::ADHERENTS
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENTS_READONLY',
            'Administrateur des adhérents en lecture seule',
            AdministratorRoleGroupEnum::ADHERENTS
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_IMPERSONATE',
            'Impersonnification des adhérents',
            AdministratorRoleGroupEnum::ADHERENTS
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_BAN',
            'Bannissement des adhérents',
            AdministratorRoleGroupEnum::ADHERENTS,
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_CONSEIL',
            'Permet de gérer les informations adhérents du conseil national',
            AdministratorRoleGroupEnum::ADHERENTS,
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_UNREGISTER',
            'Désadhésion des adhérents',
            AdministratorRoleGroupEnum::ADHERENTS
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_CERTIFICATIONS',
            'Administrateur des certifications adhérents',
            AdministratorRoleGroupEnum::ADHERENTS
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_CERTIFICATION_HISTORIES',
            'Administrateur de l\'historique des certifications adhérents',
            AdministratorRoleGroupEnum::ADHERENTS
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_DECLARED_MANDATE_HISTORIES',
            'Administrateur de l\'historique des mandats déclarés',
            AdministratorRoleGroupEnum::ADHERENTS
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_INVITATIONS',
            'Administrateur des invitations adhérents',
            AdministratorRoleGroupEnum::ADHERENTS
        );
        yield $this->createRole(
            'ROLE_ADMIN_ADHERENT_UNREGISTRATIONS',
            'Administrateur des desadhésions',
            AdministratorRoleGroupEnum::ADHERENTS
        );
        // Politique
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_ADHERENT_ELECTED_REPRESENTATIVES',
            'Administrateur des adhérents élus',
            AdministratorRoleGroupEnum::POLITIQUE
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_TECHNICAL_SKILLS',
            'Administrateur des compétences techniques (Muni)',
            AdministratorRoleGroupEnum::POLITIQUE
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_THEMES',
            'Administrateur des thèmes (Muni)',
            AdministratorRoleGroupEnum::POLITIQUE
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_TAGS',
            'Administrateur des tags de candidatures (Muni)',
            AdministratorRoleGroupEnum::POLITIQUE
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_RUNNING_MATE_REQUESTS',
            'Administrateur des candidatures colistiers',
            AdministratorRoleGroupEnum::POLITIQUE
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_VOLUNTEER_REQUESTS',
            'Administrateur des candidatures bénévoles',
            AdministratorRoleGroupEnum::POLITIQUE
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_REPUBLICAN_SILENCES',
            'Administrateur des silences républicains',
            AdministratorRoleGroupEnum::POLITIQUE
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_LEGISLATIVE_CANDIDATES',
            'Administrateur des candidats aux législatives',
            AdministratorRoleGroupEnum::POLITIQUE
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
            AdministratorRoleGroupEnum::POLITIQUE
        );
        yield $this->createRole(
            'ROLE_ADMIN_POLITIQUE_ELECTION_CITY_CARD_MANAGERS',
            'Administrateur du suivi/pilotage (élections)',
            AdministratorRoleGroupEnum::POLITIQUE
        );
        // Territoires
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_ELUS_NOTIFICATION',
            'Administrateur des notifications de déclarations de mandats',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_COMMITTEES',
            'Administrateur des comités',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_COMMITTEE_DESIGNATION',
            'Administrateur des désignations de comités',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_COMMITTEE_MERGES',
            'Administrateur des fusions de comités',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_EVENTS',
            'Administrateur des événements',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_EVENT_CATEGORIES',
            'Administrateur des catégories d\'événements',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_EVENT_GROUP_CATEGORIES',
            'Administrateur des groupes de catégories d\'événements',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_PROCURATION_REQUESTS',
            'Administrateur des demandes de procuration',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_PROCURATION_PROXIES',
            'Administrateur des propositions de procuration',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_REFERENTS',
            'Administrateur des référents',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_REFERENT_AREAS',
            'Administrateur des zones de référents',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_ORGANIZATIONAL_CHART_ITEMS',
            'Administrateur des équipes départementales',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_VOTE_PLACES',
            'Administrateur des bureaux de votes (assesseurs)',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_ASSESSOR_REQUESTS',
            'Administrateur des demandes assesseurs',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_ELECTIONS',
            'Administrateur des élections (assesseurs)',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_REPORTS',
            'Administrateur des signalements',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_CITIES',
            'Administrateur des communes',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_FILES',
            'Administrateur des documents',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_TEAMS',
            'Administrateur des équipes',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_TEAM_MEMBER_HISTORIES',
            'Administrateur de l\'historique des équipes',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_CONSULTATIONS',
            'Administrateur des consultations',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_JME_DOCUMENTS',
            'Administrateur des documents JME',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_JME_GENERAL_MEETING_REPORTS',
            'Administrateur du centre d\'archives JME',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        yield $this->createRole(
            'ROLE_ADMIN_TERRITOIRES_JME_EMAIL_TEMPLATES',
            'Administrateur des modèles d\'emails JME',
            AdministratorRoleGroupEnum::TERRITOIRES
        );
        // Application Mobile
        yield $this->createRole(
            'ROLE_ADMIN_APPLICATION_MOBILE_NOTIFICATIONS',
            'Administrateur des notifications',
            AdministratorRoleGroupEnum::APPLICATION_MOBILE
        );
        yield $this->createRole(
            'ROLE_ADMIN_APPLICATION_MOBILE_NATIONAL_NEWS',
            'Administrateur des actus principales nationales',
            AdministratorRoleGroupEnum::APPLICATION_MOBILE
        );
        yield $this->createRole(
            'ROLE_ADMIN_APPLICATION_MOBILE_REGIONAL_NEWS',
            'Administrateur des actus principales régionales',
            AdministratorRoleGroupEnum::APPLICATION_MOBILE
        );
        yield $this->createRole(
            'ROLE_ADMIN_APPLICATION_MOBILE_DEPARTMENTAL_NEWS',
            'Administrateur des actus principales départmentales',
            AdministratorRoleGroupEnum::APPLICATION_MOBILE
        );
        yield $this->createRole(
            'ROLE_ADMIN_APPLICATION_MOBILE_SUGGESTED_QUESTIONS',
            'Administrateur des questions panier ',
            AdministratorRoleGroupEnum::APPLICATION_MOBILE
        );
        yield $this->createRole(
            'ROLE_ADMIN_APPLICATION_MOBILE_LOCAL_SURVEYS',
            'Administrateur des questionnaires locaux',
            AdministratorRoleGroupEnum::APPLICATION_MOBILE
        );
        yield $this->createRole(
            'ROLE_ADMIN_APPLICATION_MOBILE_NATIONAL_SURVEYS',
            'Administrateur des questionnaires nationaux',
            AdministratorRoleGroupEnum::APPLICATION_MOBILE
        );
        yield $this->createRole(
            'ROLE_ADMIN_APPLICATION_MOBILE_NATIONAL_POLLS',
            'Administrateur des sondages',
            AdministratorRoleGroupEnum::APPLICATION_MOBILE
        );
        yield $this->createRole(
            'ROLE_ADMIN_APPLICATION_MOBILE_RIPOSTES',
            'Administrateur des ripostes',
            AdministratorRoleGroupEnum::APPLICATION_MOBILE
        );
        yield $this->createRole(
            'ROLE_ADMIN_APPLICATION_MOBILE_RESSOURCE_LINKS',
            'Administrateur des ressources',
            AdministratorRoleGroupEnum::APPLICATION_MOBILE
        );
        yield $this->createRole(
            'ROLE_ADMIN_APPLICATION_MOBILE_HEADER_BLOCKS',
            'Administrateur des paramètres d\'en-tête',
            AdministratorRoleGroupEnum::APPLICATION_MOBILE
        );
        yield $this->createRole(
            'ROLE_ADMIN_APPLICATION_MOBILE_DEEP_LINKS',
            'Administrateur des deep links',
            AdministratorRoleGroupEnum::APPLICATION_MOBILE
        );
        // Phoning
        yield $this->createRole(
            'ROLE_ADMIN_PHONING_CAMPAIGNS',
            'Administrateur des campagnes',
            AdministratorRoleGroupEnum::PHONING
        );
        yield $this->createRole(
            'ROLE_ADMIN_PHONING_CAMPAIGN_HISTORIES',
            'Administrateur des appels passés',
            AdministratorRoleGroupEnum::PHONING
        );
        // Porte à porte
        yield $this->createRole(
            'ROLE_ADMIN_PORTE_A_PORTE_CAMPAIGNS',
            'Administrateur des campagnes',
            AdministratorRoleGroupEnum::PORTE_A_PORTE
        );
        yield $this->createRole(
            'ROLE_ADMIN_PORTE_A_PORTE_CAMPAIGN_HISTORIES',
            'Administrateur des portes frappées',
            AdministratorRoleGroupEnum::PORTE_A_PORTE
        );
        // Instances
        yield $this->createRole(
            'ROLE_ADMIN_INSTANCES_TERRITORIAL_COUNCILS',
            'Administrateur des Coterr et Copol',
            AdministratorRoleGroupEnum::INSTANCES
        );
        yield $this->createRole(
            'ROLE_ADMIN_INSTANCES_TERRITORIAL_COUNCIL_MEMBERSHIPS',
            'Administrateur des membres',
            AdministratorRoleGroupEnum::INSTANCES
        );
        yield $this->createRole(
            'ROLE_ADMIN_INSTANCES_VOTING_PLATFORM_DESIGNATIONS',
            'Administrateur des désignations statutaires',
            AdministratorRoleGroupEnum::INSTANCES
        );
        yield $this->createRole(
            'ROLE_ADMIN_INSTANCES_VOTING_PLATFORM_DESIGNATION_POLLS',
            'Administrateur des questionnaires (désignation)',
            AdministratorRoleGroupEnum::INSTANCES
        );
        yield $this->createRole(
            'ROLE_ADMIN_INSTANCES_VOTING_PLATFORM_DESIGNATION_CANDIDACY_POOLS',
            'Administrateur des candidatures (désignations)',
            AdministratorRoleGroupEnum::INSTANCES
        );
        yield $this->createRole(
            'ROLE_ADMIN_INSTANCES_TERRITORIAL_COUNCIL_MEMBERSHIP_LOGS',
            'Administrateur des logs',
            AdministratorRoleGroupEnum::INSTANCES
        );
        yield $this->createRole(
            'ROLE_ADMIN_INSTANCES_DESIGNATION_ELECTIONS',
            'Administrateur des élections',
            AdministratorRoleGroupEnum::INSTANCES
        );
        yield $this->createRole(
            'ROLE_ADMIN_INSTANCES_DESIGNATION_CANDIDATURES',
            'Administrateur des candidatures',
            AdministratorRoleGroupEnum::INSTANCES
        );
        yield $this->createRole(
            'ROLE_ADMIN_INSTANCES_DESIGNATION_VOTES',
            'Administrateur des émargements',
            AdministratorRoleGroupEnum::INSTANCES
        );
        // Finances
        yield $this->createRole(
            'ROLE_ADMIN_FINANCES_DONATIONS',
            'Administrateur des dons',
            AdministratorRoleGroupEnum::FINANCES
        );
        // Tech
        yield $this->createRole(
            'ROLE_ADMIN_TECH_ADMINISTRATORS',
            'Administrateur des dons',
            AdministratorRoleGroupEnum::TECH
        );
        yield $this->createRole(
            'ROLE_ADMIN_TECH_EMAIL_LOGS',
            'Administrateur des logs d\'emails',
            AdministratorRoleGroupEnum::TECH
        );
        yield $this->createRole(
            'ROLE_ADMIN_TECH_SUBSCRIPTION_TYPES',
            'Administrateur des types d\'emails',
            AdministratorRoleGroupEnum::TECH
        );
        yield $this->createRole(
            'ROLE_ADMIN_TECH_USER_LIST_DEFINITIONS',
            'Administrateur des labels',
            AdministratorRoleGroupEnum::TECH
        );
        yield $this->createRole(
            'ROLE_ADMIN_TECH_OAUTH_CLIENTS',
            'Administrateur des clients OAuth',
            AdministratorRoleGroupEnum::TECH
        );
        yield $this->createRole(
            'ROLE_ADMIN_TECH_SCOPES',
            'Administrateur des scopes',
            AdministratorRoleGroupEnum::TECH
        );
        // Idées
        yield $this->createRole(
            'ROLE_ADMIN_IDEES_OLDOLF',
            'Administrateur OLDOLF',
            AdministratorRoleGroupEnum::IDEES
        );
        yield $this->createRole(
            'ROLE_ADMIN_IDEES_CHEZ_VOUS',
            'Administrateur ChezVous',
            AdministratorRoleGroupEnum::IDEES
        );
        // Formation
        yield $this->createRole(
            'ROLE_ADMIN_FORMATION_FORMATIONS',
            'Administrateur des formations',
            AdministratorRoleGroupEnum::FORMATION
        );
        yield $this->createRole(
            'ROLE_ADMIN_FORMATION_MOOC',
            'Administrateur des MOOC',
            AdministratorRoleGroupEnum::FORMATION
        );
        // Archives
        yield $this->createRole(
            'ROLE_ADMIN_ARCHIVES_TON_MACRON',
            'Administrateur TonMacron',
            AdministratorRoleGroupEnum::ARCHIVES
        );
        yield $this->createRole(
            'ROLE_ADMIN_ARCHIVES_MY_EUROPE',
            'Administrateur MonEurope',
            AdministratorRoleGroupEnum::ARCHIVES
        );
        yield $this->createRole(
            'ROLE_ADMIN_ARCHIVES_CONTENU',
            'Administrateur du contenu',
            AdministratorRoleGroupEnum::ARCHIVES
        );
        yield $this->createRole(
            'ROLE_ADMIN_ARCHIVES_JE_PARTAGE',
            'Administrateur JePartage',
            AdministratorRoleGroupEnum::ARCHIVES
        );
        yield $this->createRole(
            'ROLE_ADMIN_ARCHIVES_EXPLICATIONS',
            'Administrateur Explications',
            AdministratorRoleGroupEnum::ARCHIVES
        );
        yield $this->createRole(
            'ROLE_ADMIN_ARCHIVES_FACEBOOK_PROFILES',
            'Administrateur des profils Facebook',
            AdministratorRoleGroupEnum::ARCHIVES
        );
        yield $this->createRole(
            'ROLE_ADMIN_ARCHIVES_BOARD_MEMBER_ROLES',
            'Administrateur des rôles de membres du conseil',
            AdministratorRoleGroupEnum::ARCHIVES
        );
        yield $this->createRole(
            'ROLE_ADMIN_ARCHIVES_REFERENT_TAGS',
            'Administrateur des tags référent',
            AdministratorRoleGroupEnum::ARCHIVES
        );
        yield $this->createRole(
            'ROLE_ADMIN_ARCHIVES_PROPOSALS',
            'Administrateur des propositions',
            AdministratorRoleGroupEnum::ARCHIVES
        );
        yield $this->createRole(
            'ROLE_ADMIN_ARCHIVES_JE_MARCHE',
            'Administrateur JeMarche',
            AdministratorRoleGroupEnum::ARCHIVES
        );
        // Socle programmatique
        yield $this->createRole(
            'ROLE_ADMIN_SOCLE_PROGRAMMATIQUE_ALL',
            'Administrateur du socle programmatique',
            AdministratorRoleGroupEnum::SOCLE_PROGRAMMATIQUE
        );
        // Communautés thématiques
        yield $this->createRole(
            'ROLE_ADMIN_COMMUNAUTES_THEMATIQUES_ALL',
            'Administrateur des communautés thématiques',
            AdministratorRoleGroupEnum::COMMUNAUTES_THEMATIQUES
        );
        // Élections départementales
        yield $this->createRole(
            'ROLE_ADMIN_ELECTIONS_DEPARTEMENTALES_ALL',
            'Administrateur des élections départementales',
            AdministratorRoleGroupEnum::ELECTIONS_DEPARTEMENTALES
        );
    }
}

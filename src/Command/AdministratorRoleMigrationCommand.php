<?php

namespace App\Command;

use App\Entity\Administrator;
use App\Repository\AdministratorRepository;
use App\Repository\AdministratorRoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:administrator-role:migrate',
    description: 'Migrate administrator roles.',
)]
class AdministratorRoleMigrationCommand extends Command
{
    private const OLD_ROLE_TO_NEW_ROLES_MAPPING = [
        'ROLE_SUPER_ADMIN' => [
            'ROLE_SUPER_ADMIN',
        ],
        'ROLE_ADMIN_RENAISSANCE' => [
            'ROLE_ADMIN_RENAISSANCE_COMMITMENTS',
            'ROLE_ADMIN_RENAISSANCE_ARTICLES',
            'ROLE_ADMIN_RENAISSANCE_HOME_BLOCKS',
            'ROLE_ADMIN_RENAISSANCE_BIOGRAPHY_EXECUTIVE_OFFICE_MEMBERS',
            'ROLE_ADMIN_RENAISSANCE_ADHERENT_FORMATIONS',
            'ROLE_ADMIN_RENAISSANCE_DEPARTMENT_SITES',
            'ROLE_ADMIN_RENAISSANCE_CREATE_ADHERENT',
        ],
        'ROLE_ADMIN_MEDIAS' => [
            'ROLE_ADMIN_COMMUNICATION_MEDIAS',
        ],
        'ROLE_ADMIN_CONTENT' => [
            'ROLE_ADMIN_COMMUNICATION_ARTICLES',
            'ROLE_ADMIN_COMMUNICATION_ARTICLE_CATEGORIES',
            'ROLE_ADMIN_COMMUNICATION_PAGES',
            'ROLE_ADMIN_COMMUNICATION_CLARIFICATIONS',
            'ROLE_ADMIN_ARCHIVES_CONTENU',
        ],
        'ROLE_ADMIN_HOME' => [
            'ROLE_ADMIN_COMMUNICATION_HOME_BLOCKS',
            'ROLE_ADMIN_COMMUNICATION_LIVE_LINKS',
        ],
        'ROLE_ADMIN_PROPOSALS' => [
            'ROLE_ADMIN_ARCHIVES_PROPOSALS',
        ],
        'ROLE_ADMIN_ORDERS' => [
            'ROLE_ADMIN_ARCHIVES_EXPLICATIONS',
        ],
        'ROLE_ADMIN_FACEBOOK_PROFILES' => [
            'ROLE_ADMIN_ARCHIVES_FACEBOOK_PROFILES',
        ],
        'ROLE_ADMIN_REDIRECTIONS' => [
            'ROLE_ADMIN_COMMUNICATION_REDIRECTIONS',
        ],
        'ROLE_ADMIN_NEWSLETTER' => [
            'ROLE_ADMIN_COMMUNICATION_NEWSLETTER_SUBSCRIPTIONS',
            'ROLE_ADMIN_ADHERENT_INVITATIONS',
        ],
        'ROLE_ADMIN_JE_MARCHE' => [
            'ROLE_ADMIN_ARCHIVES_JE_MARCHE',
        ],
        'ROLE_ADMIN_TON_MACRON' => [
            'ROLE_ADMIN_ARCHIVES_TON_MACRON',
        ],
        'ROLE_ADMIN_MY_EUROPE' => [
            'ROLE_ADMIN_ARCHIVES_MY_EUROPE',
        ],
        'ROLE_ADMIN_LEGISLATIVES' => [
            'ROLE_ADMIN_POLITIQUE_LEGISLATIVE_CANDIDATES',
            'ROLE_ADMIN_POLITIQUE_LEGISLATIVE_DISTRICT_ZONES',
        ],
        'ROLE_ADMIN_ADHERENTS' => [
            'ROLE_ADMIN_ADHERENT_ADHERENTS',
            'ROLE_ADMIN_ADHERENT_IMPERSONATE',
            'ROLE_ADMIN_ADHERENT_BAN',
        ],
        'ROLE_ADMIN_ADHERENTS_READONLY' => [
            'ROLE_ADMIN_ADHERENTS_READONLY',
        ],
        'ROLE_ADMIN_ADHERENT_ELECTED_REPRESENTATIVES' => [
            'ROLE_ADMIN_POLITIQUE_ADHERENT_ELECTED_REPRESENTATIVES',
        ],
        'ROLE_ADMIN_UNREGISTRATIONS' => [
            'ROLE_ADMIN_ADHERENT_UNREGISTRATIONS',
        ],
        'ROLE_ADMIN_COMMITTEES' => [
            'ROLE_ADMIN_TERRITOIRES_COMMITTEES',
        ],
        'ROLE_ADMIN_COMMITTEE_DESIGNATION' => [
            'ROLE_ADMIN_TERRITOIRES_COMMITTEE_DESIGNATION',
        ],
        'ROLE_ADMIN_COMMITTEES_MERGE' => [
            'ROLE_ADMIN_TERRITOIRES_COMMITTEE_MERGES',
        ],
        'ROLE_ADMIN_EVENTS' => [
            'ROLE_ADMIN_TERRITOIRES_EVENTS',
            'ROLE_ADMIN_TERRITOIRES_EVENT_CATEGORIES',
        ],
        'ROLE_ADMIN_PROCURATIONS' => [
            'ROLE_ADMIN_TERRITOIRES_PROCURATION_REQUESTS',
            'ROLE_ADMIN_TERRITOIRES_PROCURATION_PROXIES',
        ],
        'ROLE_ADMIN_ELECTIONS' => [
            'ROLE_ADMIN_TERRITOIRES_ELECTIONS',
        ],
        'ROLE_ADMIN_FINANCE' => [
            'ROLE_ADMIN_FINANCES_DONATIONS',
        ],
        'ROLE_ADMIN_CREATE_RENAISSANCE_ADHERENT' => [
            'ROLE_ADMIN_RENAISSANCE_CREATE_ADHERENT',
        ],
        'ROLE_ADMIN_LOCAL_ELECTION' => [
            'ROLE_ADMIN_ELECTIONS_DEPARTEMENTALES_ALL',
        ],
        'ROLE_ADMIN_EMAIL' => [
            'ROLE_ADMIN_TECH_EMAIL_LOGS',
        ],
        'ROLE_ADMIN_ORGANIGRAMM' => [
            'ROLE_ADMIN_TERRITOIRES_ORGANIZATIONAL_CHART_ITEMS',
        ],
        'ROLE_ADMIN_REFERENT_TAGS' => [
            'ROLE_ADMIN_ARCHIVES_REFERENT_TAGS',
        ],
        'ROLE_ADMIN_REFERENTS' => [
            'ROLE_ADMIN_TERRITOIRES_REFERENTS',
        ],
        'ROLE_ADMIN_REFERENTS_AREAS' => [
            'ROLE_ADMIN_TERRITOIRES_REFERENT_AREAS',
        ],
        'ROLE_ADMIN_REPORTS' => [
            'ROLE_ADMIN_TERRITOIRES_REPORTS',
        ],
        'ROLE_ADMIN_TIMELINE' => [
            'ROLE_ADMIN_IDEES_OLDOLF',
        ],
        'ROLE_ADMIN_MOOC' => [
            'ROLE_ADMIN_FORMATION_MOOC',
        ],
        'ROLE_ADMIN_CLIENTS' => [
            'ROLE_ADMIN_TECH_OAUTH_CLIENTS',
        ],
        'ROLE_ADMIN_EMAIL_SUBSCRIPTION_TYPES' => [
            'ROLE_ADMIN_TECH_SUBSCRIPTION_TYPES',
        ],
        'ROLE_ADMIN_LABEL' => [
            'ROLE_ADMIN_TECH_USER_LIST_DEFINITIONS',
        ],
        'ROLE_ADMIN_BIOGRAPHY' => [
            'ROLE_ADMIN_COMMUNICATION_BIOGRAPHY_EXEXECUTIVE_OFFICE_MEMBERS',
        ],
        'ROLE_ADMIN_JECOUTE' => [
            'ROLE_ADMIN_APPLICATION_MOBILE_NOTIFICATIONS',
            'ROLE_ADMIN_APPLICATION_MOBILE_NATIONAL_NEWS',
            'ROLE_ADMIN_APPLICATION_MOBILE_REGIONAL_NEWS',
            'ROLE_ADMIN_APPLICATION_MOBILE_DEPARTMENTAL_NEWS',
            'ROLE_ADMIN_APPLICATION_MOBILE_SUGGESTED_QUESTIONS',
            'ROLE_ADMIN_APPLICATION_MOBILE_LOCAL_SURVEYS',
            'ROLE_ADMIN_APPLICATION_MOBILE_NATIONAL_SURVEYS',
            'ROLE_ADMIN_APPLICATION_MOBILE_NATIONAL_POLLS',
            'ROLE_ADMIN_APPLICATION_MOBILE_RIPOSTES',
            'ROLE_ADMIN_APPLICATION_MOBILE_RESSOURCE_LINKS',
            'ROLE_ADMIN_APPLICATION_MOBILE_HEADER_BLOCKS',
            'ROLE_ADMIN_APPLICATION_MOBILE_DEEP_LINKS',
        ],
        'ROLE_ADMIN_BAN' => [
            'ROLE_ADMIN_ADHERENT_BAN',
        ],
        'ROLE_ADMIN_CERTIFICATION' => [
            'ROLE_ADMIN_ADHERENT_CERTIFICATIONS',
            'ROLE_ADMIN_ADHERENT_CERTIFICATION_HISTORIES',
        ],
        'ROLE_ADMIN_FORMATION' => [
            'ROLE_ADMIN_FORMATION_FORMATIONS',
        ],
        'ROLE_ADMIN_ASSESSOR' => [
            'ROLE_ADMIN_TERRITOIRES_VOTE_PLACES',
            'ROLE_ADMIN_TERRITOIRES_ASSESSOR_REQUESTS',
        ],
        'ROLE_ADMIN_APPLICATION_REQUEST' => [
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_TECHNICAL_SKILLS',
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_THEMES',
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_TAGS',
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_RUNNING_MATE_REQUESTS',
            'ROLE_ADMIN_POLITIQUE_APPLICATION_REQUEST_VOLUNTEER_REQUESTS',
        ],
        'ROLE_ADMIN_CHEZ_VOUS' => [
            'ROLE_ADMIN_IDEES_CHEZ_VOUS',
        ],
        'ROLE_ADMIN_PROGRAMMATIC_FOUNDATION' => [
            'ROLE_ADMIN_SOCLE_PROGRAMMATIQUE_ALL',
        ],
        'ROLE_ADMIN_ELECTION_CITY_CARD' => [
            'ROLE_ADMIN_POLITIQUE_ELECTION_CITY_CARDS',
        ],
        'ROLE_ADMIN_ELECTION_CITY_CARD_MANAGERS' => [
            'ROLE_ADMIN_POLITIQUE_ELECTION_CITY_CARDS',
            'ROLE_ADMIN_POLITIQUE_ELECTION_CITY_CARD_MANAGERS',
        ],
        'ROLE_ADMIN_ELECTED_REPRESENTATIVE' => [
            'ROLE_ADMIN_POLITIQUE_ELECTED_REPRESENTATIVES',
        ],
        'ROLE_ADMIN_TERRITORIAL_COUNCIL' => [
            'ROLE_ADMIN_INSTANCES_TERRITORIAL_COUNCILS',
            'ROLE_ADMIN_INSTANCES_TERRITORIAL_COUNCIL_MEMBERSHIPS',
            'ROLE_ADMIN_INSTANCES_DESIGNATION_ELECTIONS',
            'ROLE_ADMIN_INSTANCES_DESIGNATION_CANDIDATURES',
            'ROLE_ADMIN_INSTANCES_DESIGNATION_VOTES',
        ],
        'ROLE_ADMIN_TERRITORIAL_COUNCIL_MEMBERSHIP_LOG' => [
            'ROLE_ADMIN_INSTANCES_TERRITORIAL_COUNCIL_MEMBERSHIP_LOGS',
        ],
        'ROLE_ADMIN_THEMATIC_COMMUNITY' => [
            'ROLE_ADMIN_COMMUNAUTES_THEMATIQUES_ALL',
        ],
        'ROLE_ADMIN_FILES' => [
            'ROLE_ADMIN_TERRITOIRES_FILES',
        ],
        'ROLE_ADMIN_SCOPES' => [
            'ROLE_ADMIN_TECH_SCOPES',
        ],
        'ROLE_ADMIN_TEAMS' => [
            'ROLE_ADMIN_TERRITOIRES_TEAMS',
            'ROLE_ADMIN_TERRITOIRES_TEAM_MEMBER_HISTORIES',
        ],
        'ROLE_ADMIN_PHONING_CAMPAIGNS' => [
            'ROLE_ADMIN_PHONING_CAMPAIGNS',
            'ROLE_ADMIN_PHONING_CAMPAIGN_HISTORIES',
        ],
        'ROLE_ADMIN_PAP_CAMPAIGNS' => [
            'ROLE_ADMIN_PORTE_A_PORTE_CAMPAIGNS',
        ],
        'ROLE_ADMIN_SMS_CAMPAIGNS' => [
            'ROLE_ADMIN_COMMUNICATION_SMS_CAMPAIGNS',
        ],
        'ROLE_ADMIN_QR_CODES' => [
            'ROLE_ADMIN_COMMUNICATION_QR_CODES',
        ],
        'ROLE_ADMIN_CMS_BLOCKS' => [
            'ROLE_ADMIN_COMMUNICATION_CMS_BLOCKS',
        ],
        'ROLE_ADMIN_ADMINISTRATORS' => [
            'ROLE_ADMIN_TECH_ADMINISTRATORS',
        ],
        'ROLE_ADMIN_JEMENGAGE_COM' => [
            'ROLE_ADMIN_APPLICATION_MOBILE_NOTIFICATIONS',
            'ROLE_ADMIN_APPLICATION_MOBILE_RIPOSTES',
            'ROLE_ADMIN_APPLICATION_MOBILE_RESSOURCE_LINKS',
        ],
        'ROLE_ADMIN_JME_DOCUMENTS' => [
            'ROLE_ADMIN_TERRITOIRES_JME_DOCUMENTS',
        ],
        'ROLE_ADMIN_JME_GENERAL_MEETING_REPORT' => [
            'ROLE_ADMIN_TERRITOIRES_JME_GENERAL_MEETING_REPORTS',
        ],
        'ROLE_ADMIN_JME_EMAIL_TEMPLATE' => [
            'ROLE_ADMIN_TERRITOIRES_JME_EMAIL_TEMPLATES',
        ],
        'ROLE_ADMIN_ELUS_NOTIFICATION' => [
            'ROLE_ADMIN_TERRITOIRES_ELUS_NOTIFICATION',
        ],
        'ROLE_APP_ADMIN_ADHERENT_CONSEIL' => [
            'ROLE_ADMIN_ADHERENT_CONSEIL',
        ],
    ];

    /** @var SymfonyStyle */
    private $io;

    private array $administratorRoles = [];

    public function __construct(
        private readonly AdministratorRepository $administratorRepository,
        private readonly AdministratorRoleRepository $administratorRoleRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm but will not persist in database.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRunMode = $input->getOption('dry-run');

        if ($dryRunMode) {
            $this->io->note('Dry-Run Mode is ON.');
        }

        $this->loadAdministratorRoles();
        $administrators = $this->getAdministrators();

        $count = \count($administrators);

        if (0 === $count) {
            $this->io->note('No administrator to process.');

            return self::SUCCESS;
        }

        if (false === $this->io->confirm(sprintf('Are you sure to migrate roles of %d administrators?', $count), false)) {
            return self::FAILURE;
        }

        $this->io->progressStart($count);

        foreach ($administrators as $administrator) {
            $this->migrateRoles($administrator);

            $this->io->progressAdvance();
        }

        if (!$dryRunMode) {
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    /**
     * @return Administrator[]
     */
    private function getAdministrators(): array
    {
        return $this->administratorRepository->findAll();
    }

    private function loadAdministratorRoles(): void
    {
        $this->administratorRoles = [];

        foreach ($this->administratorRoleRepository->findAll() as $role) {
            $this->administratorRoles[$role->code] = $role;
        }
    }

    private function migrateRoles(Administrator $administrator): void
    {
        foreach ($administrator->roles as $oldRole) {
            if ('ROLE_ADMIN_DASHBOARD' === $oldRole) {
                continue;
            }

            if (!\array_key_exists($oldRole, self::OLD_ROLE_TO_NEW_ROLES_MAPPING)) {
                $this->io->note(sprintf('Old role with code "%s" was not found in mapping. Skipping.', $oldRole));

                continue;
            }

            foreach (self::OLD_ROLE_TO_NEW_ROLES_MAPPING[$oldRole] as $newRole) {
                if (!\array_key_exists($newRole, $this->administratorRoles)) {
                    $this->io->note(sprintf('New role with code "%s" was not found in database. Skipping', $newRole));

                    continue;
                }

                $administrator->addAdministratorRole($this->administratorRoles[$newRole]);
            }
        }
    }
}

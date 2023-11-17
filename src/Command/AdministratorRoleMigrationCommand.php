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
    private const ROLES_MAPPING = [
        'ROLE_SUPER_ADMIN' => [
            'ROLE_SUPER_ADMIN',
        ],
        'ROLE_ADMIN_ADHERENTS' => [
            'ROLE_ADMIN_ADHERENT_ADHERENTS',
            'ROLE_ADMIN_ADHERENT_IMPERSONATE',
        ],
        'ROLE_ADMIN_BAN' => [
            'ROLE_ADMIN_ADHERENT_BAN',
        ],
        'ROLE_ADMIN_CERTIFICATION' => [
            'ROLE_ADMIN_ADHERENT_CERTIFICATIONS',
            'ROLE_ADMIN_ADHERENT_CERTIFICATION_HISTORIES',
        ],
        // ...
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
            if (!\array_key_exists($oldRole, self::ROLES_MAPPING)) {
                $this->io->note(sprintf('Old role with code "%s" was not found in mapping. Skipping.', $oldRole));

                continue;
            }

            foreach (self::ROLES_MAPPING[$oldRole] as $newRole) {
                if (!\array_key_exists($newRole, $this->administratorRoles)) {
                    $this->io->note(sprintf('New role with code "%s" was not found in database. Skipping', $newRole));

                    continue;
                }

                $administrator->addAdministratorRole($this->administratorRoles[$newRole]);
            }
        }
    }
}

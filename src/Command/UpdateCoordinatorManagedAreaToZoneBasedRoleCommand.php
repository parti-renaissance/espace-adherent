<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\AdherentZoneBasedRole;
use App\Entity\Geo\Zone;
use App\Repository\AdherentRepository;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateCoordinatorManagedAreaToZoneBasedRoleCommand extends Command
{
    protected static $defaultName = 'app:adherent:coordinator-zone-migrate';

    private SymfonyStyle $io;
    private AdherentRepository $adherentRepository;
    private ZoneRepository $zoneRepository;
    private ObjectManager $entityManager;

    public function __construct(
        AdherentRepository $adherentRepository,
        ZoneRepository $zoneRepository,
        ObjectManager $entityManager
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->zoneRepository = $zoneRepository;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate coordinator managed committees areas to Zone based role')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');

        $paginator = $this->getQueryBuilder();
        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(sprintf('Are you sure to update %d Coordinator(s) account(s)?', $total), false)) {
            return 1;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 100 ? $limit : 100);

        $this->io->progressStart($total);
        $offset = 0;

        $this->entityManager->beginTransaction();
        do {
            /** @var Adherent $adherent */
            foreach ($paginator as $adherent) {
                try {
                    $regions = [];
                    foreach (array_map('trim', explode(',', $adherent->getCoordinatorCommitteeArea()->__toString())) as $code) {
                        /** @var Zone $zone */
                        $zone = $this->zoneRepository->findOneByCode($code);
                        $zoneRegions = $zone->getParentsOfType(Zone::REGION);
                        $region = !empty($zoneRegions) ? current($zoneRegions) : null;

                        if ($region && !\in_array($region, $regions, true)) {
                            $regions[] = $region;
                        }
                    }

                    $adherent->addZoneBasedRole(AdherentZoneBasedRole::createRegionalCoordinator($regions));

                    $this->entityManager->flush();
                } catch (\Exception $e) {
                    $this->io->comment(sprintf(
                        'Error while updating Coordinator account "%s". Message: "%s".',
                        $adherent->getId(),
                        $e->getMessage()
                    ));
                }

                $this->io->progressAdvance();
                ++$offset;
                if ($limit && $limit <= $offset) {
                    break 2;
                }
            }

            $paginator->getQuery()->setFirstResult($offset);
        } while ($offset < $count && (!$limit || $offset < $limit));

        $this->entityManager->flush();
        $this->entityManager->commit();

        $this->io->progressFinish();
        $this->io->note($offset.' account(s) updated');

        return 0;
    }

    private function getQueryBuilder(): Paginator
    {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->leftJoin('adherent.coordinatorCommitteeArea', 'coordinatorCommitteeArea')
            ->where('coordinatorCommitteeArea IS NOT NULL AND coordinatorCommitteeArea.sector = :sector')
            ->setParameter('sector', 'committee_sector')
        ;

        return new Paginator($queryBuilder->getQuery());
    }
}

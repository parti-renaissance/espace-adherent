<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\AdherentZoneBasedRole;
use App\Repository\AdherentRepository;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Oneshot command, can be deleted after execution.
 */
class CreateDeputyZoneBasedRoleCommand extends Command
{
    private const BATCH_SIZE = 1000;

    protected static $defaultName = 'app:zone-based-role:create-deputies';

    private EntityManagerInterface $em;
    private AdherentRepository $adherentRepository;
    private ZoneRepository $zoneRepository;
    private SymfonyStyle $io;

    public function __construct(
        EntityManagerInterface $em,
        AdherentRepository $adherentRepository,
        ZoneRepository $zoneRepository
    ) {
        $this->em = $em;
        $this->adherentRepository = $adherentRepository;
        $this->zoneRepository = $zoneRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create deputy zone based role from old Adherent\'s managedDistrict property.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Starting creating deputy zone based roles.');

        $this->io->progressStart($this->deputiesCount());

        $count = 0;
        foreach ($this->getDeputies() as $result) {
            /* @var Adherent $adherent */
            $adherent = $result[0];

            $zone = $this->zoneRepository->findOneBy(['geoData' => $district = $adherent->getManagedDistrict()]);

            if (!$zone) {
                $this->io->warning(sprintf(
                    'No geo zone found for Adherent with id %s and managed district id %s',
                    $adherent->getId(),
                    $district->getId()
                ));

                $this->io->progressAdvance();

                continue;
            }

            $adherent->addZoneBasedRole(AdherentZoneBasedRole::createDeputy($zone));

            $this->em->flush();

            if (0 === (++$count % self::BATCH_SIZE)) {
                $this->em->clear();
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success('Creating deputy zone based roles has been finished successfully.');

        return 0;
    }

    private function getDeputies(): IterableResult
    {
        return $this
            ->createDeputyProxyQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function deputiesCount(): int
    {
        return $this
            ->createDeputyProxyQueryBuilder()
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createDeputyProxyQueryBuilder(): QueryBuilder
    {
        return $this
            ->adherentRepository
            ->createQueryBuilder('a')
            ->addSelect('managedDistrict')
            ->leftJoin('a.managedDistrict', 'managedDistrict')
            ->where('managedDistrict.id IS NOT NULL')
        ;
    }
}

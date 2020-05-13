<?php

namespace App\Command;

use App\ElectedRepresentative\ElectedRepresentativeMandatesOrderer;
use App\Entity\City;
use App\Entity\Department;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\ElectedRepresentative\Zone;
use App\Repository\ElectedRepresentative\MandateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Oneshot command to complete the migration of "elected_representatives_register" table.
 */
class RestructureElectedRepresentativesRegisterCommand extends Command
{
    private const BATCH_SIZE = 1000;

    /** @var EntityManagerInterface */
    private $em;
    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $electedRepresentativeRepository;
    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $zoneRepository;
    /** @var MandateRepository */
    private $mandateRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->electedRepresentativeRepository = $em->getRepository(ElectedRepresentative::class);
        $this->mandateRepository = $em->getRepository(Mandate::class);
        $this->zoneRepository = $em->getRepository(Zone::class);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:elected-representative:complete-migration')
            ->setDescription('Complete the migration of "elected_representatives_register" table')
            ->addOption(
                'only-mandates',
                null,
                InputOption::VALUE_NONE
            )
            ->addOption(
                'only-epci',
                null,
                InputOption::VALUE_NONE
            )
            ->addOption(
                'only-cities',
                null,
                InputOption::VALUE_NONE
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $onlyEpci = $input->getOption('only-epci');
        $onlyCities = $input->getOption('only-cities');
        $onlyMandates = $input->getOption('only-mandates');

        $connection = $this->em->getConnection();
        $connection->getConfiguration()->setSQLLogger(null);

        try {
            // ordering mandates
            if (!$onlyCities && !$onlyEpci) {
                $this->orderMandates($output, true);
                $this->orderMandates($output);
            }

            // adding EPCI zones to mandates
            if (!$onlyCities && !$onlyMandates) {
                $this->addZonesEpci($output);
            }

            // adding City zones to mandates
            if (!$onlyEpci && !$onlyMandates) {
                $this->addZonesCity($output);
                $this->addZonesCityTheRest($output);
                $output->writeln(['', 'City zones have been successfully added!']);
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    private function orderMandates(OutputInterface $output, bool $equals2 = false): void
    {
        $output->writeln(['', sprintf('Starting ordering mandates for an elected representative with %s.', ($equals2 ? 'only 2 mandates' : 'more than 2 mandates'))]);
        $progressBar = new ProgressBar($output, $this->countElectedRepresentative($equals2));

        $count = 0;
        foreach ($this->getElectedRepresentatives($equals2) as $id) {
            $electedRepresentative = $this->electedRepresentativeRepository->find($id);
            if ($electedRepresentative->getMandates()->count() > 1) {
                $mandates = ElectedRepresentativeMandatesOrderer::updateOrder($electedRepresentative->getMandates());
            }

            foreach ($mandates as $mandate) {
                $this->em->merge($mandate);
            }
            $this->em->merge($electedRepresentative);
            $this->em->flush();

            if (0 === ($count % self::BATCH_SIZE)) {
                $progressBar->advance(1000);
                $this->em->flush();
                $this->em->clear(); // Detaches all objects from Doctrine for memory save
                gc_collect_cycles();
            }

            ++$count;
        }

        $progressBar->finish();

        $this->em->flush();
        $this->em->clear();

        $output->writeln(['', "Finish $count ElectedRepresentative entities."]);
        $output->writeln(['', 'Mandates has been successfully ordered!']);
    }

    private function addZonesEpci(OutputInterface $output): void
    {
        $output->writeln(['', 'Starting adding EPCI zones.']);
        $progressBar = new ProgressBar($output, $this->countMandatesWithoutEpci());

        $count = 0;
        foreach ($this->getMandatesWithoutEpci() as $arrMandate) {
            extract($arrMandate);
            /** @var Mandate $mandate */
            $mandate = $this->mandateRepository->find($id);
            $words = explode(' ', $epci);
            $words = array_filter($words, function ($word) {
                return false === strpos($word, '(');
            });
            $qb = $this->zoneRepository->createQueryBuilder('zone');
            $epciExpression = $qb->expr()->andX();
            foreach ($words as $key => $word) {
                $epciExpression->add(sprintf('zone.name LIKE :word_%s', $key));
                $qb->setParameter('word_'.$key, '%'.$word.'%');
            }
            try {
                /** @var Zone $zone */
                $zone = $qb
                    ->where('zone.category = 2')
                    ->andWhere($epciExpression)
                    ->getQuery()
                    ->getOneOrNullResult()
                ;

                if ($zone) {
                    $mandate->setZone($zone);
                    $this->em->merge($zone);
                    $this->em->merge($mandate);
                    $this->em->flush();
                }
            } catch (\Exception $e) {
            }

            if (0 === ($count % self::BATCH_SIZE)) {
                $progressBar->advance(1000);
                $this->em->clear(); // Detaches all objects from Doctrine for memory save
                gc_collect_cycles();
            }

            ++$count;
        }

        $progressBar->finish();

        $this->em->flush();
        $this->em->clear();

        $output->writeln(['', "Finish $count Mandates without EPCI zone."]);
        $output->writeln(['', 'EPCI zones have been successfully added!']);
    }

    private function addZonesCity(OutputInterface $output): void
    {
        $output->writeln(['', 'Starting adding City zones.']);
        $progressBar = new ProgressBar($output, $this->countMandatesWithoutCity());

        $count = 0;
        foreach ($this->getMandatesWithoutCity() as $arrMandate) {
            extract($arrMandate);
            /** @var Mandate $mandate */
            $mandate = $this->mandateRepository->find($id);
            try {
                if ('' === $dpt or null === $dpt or '0' === $dpt) {
                    if ('Réunion' === $department) {
                        $department2 = 'La Réunion';
                    } else {
                        $department2 = str_replace(' ', '-', $department);
                    }

                    /** @var Zone $zone */
                    $zone = $this->zoneRepository->createQueryBuilder('zone')
                        ->leftJoin(City::class, 'city', Join::WITH, "zone.name = CONCAT(city.name,' (',city.postalCodes,')')")
                        ->leftJoin(Department::class, 'dpt', Join::WITH, 'city.department = dpt.id')
                        ->where('zone.category = 1')
                        ->andWhere('zone.name LIKE :city')
                        ->andWhere('(dpt.name = :department OR dpt.name = :department2)')
                        ->setParameter('city', $city)
                        ->setParameter('department', $department)
                        ->setParameter('department2', $department2)
                        ->getQuery()
                        ->getOneOrNullResult()
                    ;
                } elseif (false !== strpos($city, ')')) {
                    preg_match('#\((.*?)\)#', $city, $matches);
                    $arr = explode(' (', $city);
                    $city = $arr[0];

                    $city2 = $matches[1].' '.$city.' ('.$dpt.'%';
                    $city3 = $matches[1].'-'.$city.' ('.$dpt.'%';
                    $city4 = $matches[1].' '.str_replace(' ', '-', $city).' ('.$dpt.'%';
                    $city5 = $matches[1].$city.' ('.$dpt.'%';
                    $city6 = $city.' ('.$dpt.'%';

                    /** @var Zone $zone */
                    $zone = $this->zoneRepository->createQueryBuilder('zone')
                        ->where('zone.category = 1')
                        ->andWhere('(zone.name LIKE :city2 OR zone.name LIKE :city3 OR zone.name LIKE :city4 OR zone.name LIKE :city5 OR zone.name LIKE :city6)')
                        ->setParameter('city2', $city2)
                        ->setParameter('city3', $city3)
                        ->setParameter('city4', $city4)
                        ->setParameter('city5', $city5)
                        ->setParameter('city6', $city6)
                        ->getQuery()
                        ->getOneOrNullResult()
                    ;
                } else {
                    $qb = $this->zoneRepository->createQueryBuilder('zone')
                        ->where('zone.category = 1')
                    ;

                    if (false !== strpos($city, 'oe')) {
                        $qb->andWhere('(zone.name LIKE :city OR zone.name LIKE :city2)')
                            ->setParameter('city', $city)
                            ->setParameter('city2', str_replace('oe', 'œ', $city))
                        ;
                    } elseif (substr_count($city, ' ') > 1) {
                        $words = explode(' (', $city);
                        $qb->andWhere('(zone.name LIKE :city OR zone.name LIKE :city2)')
                            ->setParameter('city', $city)
                            ->setParameter('city2', str_replace(' ', '-', $words[0]).' ('.$words[1])
                        ;
                    } else {
                        $qb->andWhere('zone.name LIKE :city')
                            ->setParameter('city', $city)
                        ;
                    }
                    /** @var Zone $zone */
                    $zone = $qb->getQuery()
                        ->getOneOrNullResult()
                    ;
                }

                if ($zone) {
                    $mandate->setZone($zone);
                    $this->em->merge($zone);
                    $this->em->merge($mandate);
                } else {
//                    dump('null ', $city);
                }
            } catch (\Exception $e) {
//                dump('city doublon', $city);
            }

            if (0 === ($count % self::BATCH_SIZE)) {
                $progressBar->advance(1000);
                $this->em->flush();
                $this->em->clear(); // Detaches all objects from Doctrine for memory save
                gc_collect_cycles();
            }

            ++$count;
        }

        $this->em->flush();
        $this->em->clear();

        $progressBar->finish();

        $output->writeln(['', "Finish $count Mandates without City zone."]);
    }

    private function addZonesCityTheRest(OutputInterface $output): void
    {
        $output->writeln(['', 'Starting adding City zones (the rest).']);
        $progressBar = new ProgressBar($output, $this->countMandatesWithoutCity());

        $count = 0;
        foreach ($this->getMandatesWithoutCity() as $arrMandate) {
            extract($arrMandate);
            /** @var Mandate $mandate */
            $mandate = $this->mandateRepository->find($id);
            try {
                /** @var Zone $zone */
                $zone = $this->zoneRepository->createQueryBuilder('zone')
                    ->where('zone.category = 1')
                    ->andWhere('zone.name LIKE :city')
                    ->setParameter('city', $commune.' ('.$dpt.'%)')
                    ->getQuery()
                    ->getOneOrNullResult()
                ;

                if (null === $zone) {
                    $zone = $this->zoneRepository->createQueryBuilder('zone')
                        ->where('zone.category = 1')
                        ->andWhere('zone.name LIKE :city2')
                        ->setParameter('city2', '%'.$commune.'% ('.$dpt.'%)')
                        ->getQuery()
                        ->getOneOrNullResult()
                    ;
                }

                if ($zone) {
                    $mandate->setZone($zone);
                    $this->em->merge($zone);
                    $this->em->merge($mandate);
                }
            } catch (\Exception $e) {
            }

            if (0 === ($count % self::BATCH_SIZE)) {
                $progressBar->advance(1000);
                $this->em->flush();
                $this->em->clear(); // Detaches all objects from Doctrine for memory save
                gc_collect_cycles();
            }

            ++$count;
        }

        $this->em->flush();
        $this->em->clear();

        $progressBar->finish();

        $output->writeln(['', "Finish $count Mandates without City zone."]);
    }

    private function getElectedRepresentatives(bool $equals2 = false): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'er');
        $sql = <<<'SQL'
            SELECT DISTINCT(er.id) FROM elected_representative er
            LEFT JOIN
                (
                    SELECT  COUNT(id) AS nb, elected_representative_id
                    FROM    elected_representative_mandate
                    GROUP   BY elected_representative_id
                    HAVING nb %condition% 2
                ) mandate ON er.id = mandate.elected_representative_id
            WHERE mandate.nb %condition% 2
SQL
        ;

        $sql = str_replace('%condition%', $equals2 ? '=' : '>', $sql);

        $query = $this->em->createNativeQuery($sql, $rsm);
        $ids = $query->getResult();

        array_walk($ids, function (&$item) {
            $item = (int) $item['er'];
        });

        return $ids;
    }

    private function countElectedRepresentative(bool $equals2 = false): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('er_nb', 'er');
        $sql = <<<'SQL'
            SELECT COUNT(er.id) AS er_nb FROM elected_representative er
            LEFT JOIN
                (
                    SELECT  COUNT(id) AS nb, elected_representative_id
                    FROM    elected_representative_mandate
                    GROUP   BY elected_representative_id
                    HAVING nb %condition% 2
                ) mandate ON er.id = mandate.elected_representative_id
            WHERE mandate.nb %condition% 2
SQL
        ;

        $sql = str_replace('%condition%', $equals2 ? '=' : '>', $sql);

        $query = $this->em->createNativeQuery($sql, $rsm);
        $count = $query->getResult();

        return $count[0]['er'];
    }

    private function countMandatesWithoutEpci(): int
    {
        return (int) $this->mandateRepository
            ->createQueryBuilder('mandate')
            ->select('COUNT(mandate.id)')
            ->where("mandate.type = 'membre_EPCI'")
            ->andWhere('mandate.zone IS NULL')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getMandatesWithoutEpci(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('epci', 'epci');
        $sql = <<<'SQL'
            SELECT id, epci FROM elected_representative_mandate mandate
            WHERE type = 'membre_EPCI' AND zone_id IS NULL
        ;
SQL
        ;

        return $this->em->createNativeQuery($sql, $rsm)->getResult();
    }

    private function countMandatesWithoutCity(): int
    {
        return (int) $this->mandateRepository
            ->createQueryBuilder('mandate')
            ->select('COUNT(mandate.id)')
            ->where("mandate.type = 'conseiller_municipal'")
            ->andWhere('mandate.zone IS NULL')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getMandatesWithoutCity(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('ville', 'city');
        $rsm->addScalarResult('commune_nom', 'commune');
        $rsm->addScalarResult('dpt', 'dpt');
        $rsm->addScalarResult('dpt_nom', 'department');
        $sql = <<<'SQL'
            SELECT id, ville, commune_nom, dpt, dpt_nom FROM elected_representative_mandate mandate
            WHERE type = 'conseiller_municipal' AND zone_id IS NULL
SQL
        ;

        return $this->em->createNativeQuery($sql, $rsm)->getResult();
    }
}

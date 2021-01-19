<?php

namespace App\Command\Geo;

use App\Entity\AddressHolderInterface;
use App\Entity\Geo\Zone;
use App\Entity\ZoneableEntity;
use App\Geo\ZoneMatcher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ZoneMatcherCommand extends Command
{
    private const BULK = 500;

    protected static $defaultName = 'app:geo:zone-matcher';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ZoneMatcher
     */
    private $zoneMatcher;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $em, ZoneMatcher $zoneMatcher)
    {
        $this->em = $em;
        $this->zoneMatcher = $zoneMatcher;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Assigns zones to entities')
            ->addArgument(
                'entity',
                InputArgument::REQUIRED,
                sprintf('Entity fully qualified class name (must implements %s and %s).', AddressHolderInterface::class, ZoneableEntity::class)
            )
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Entity id.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm but will not persist in database.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Start assigning zones');

        $class = $input->getArgument('entity');
        $this->validateEntityClass($class);

        $id = $input->getOption('id');

        $dryRun = $input->getOption('dry-run');

        $this->io->progressStart($id ? 1 : $this->countItems($class));

        for ($offset = 0; $items = $this->getItems($class, $offset, $id); $offset += \count($items)) {
            foreach ($items as $entity) {
                $postalAddress = $entity->getPostAddress();
                if (!$postalAddress) {
                    $this->io->progressAdvance();

                    continue;
                }

                $zones = $this->zoneMatcher->match($postalAddress);
                if (0 === \count($zones)) {
                    $this->io->progressAdvance();

                    continue;
                }

                $this->applyZones($entity, $zones);

                $this->io->progressAdvance();
            }

            if (!$dryRun) {
                if ($this->io->isVeryVerbose()) {
                    $this->io->comment(sprintf('Persisting bulk in database (%d items)', \count($items)));
                }

                $this->em->flush();
            }

            $this->em->clear();
        }

        $this->io->progressFinish();

        if ($dryRun) {
            $this->io->warning('Nothing was persisted in database');
        } else {
            $this->io->success('Done');
        }

        return 0;
    }

    private function validateEntityClass(string $class): void
    {
        if (!is_subclass_of($class, AddressHolderInterface::class)) {
            throw new \InvalidArgumentException(sprintf('Class %s must implements %s', $class, AddressHolderInterface::class));
        }

        if (!is_subclass_of($class, ZoneableEntity::class)) {
            throw new \InvalidArgumentException(sprintf('Class %s must implements %s', $class, ZoneableEntity::class));
        }
    }

    private function countItems(string $class): int
    {
        /** @var ServiceEntityRepository $repository */
        $repository = $this->em->getRepository($class);

        return $repository
            ->createQueryBuilder('x')
            ->select('COUNT(DISTINCT x.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return AddressHolderInterface[]|ZoneableEntity[]
     */
    private function getItems(string $class, int $offset, ?string $id): array
    {
        /** @var ServiceEntityRepository $repository */
        $repository = $this->em->getRepository($class);

        $queryBuilder = $repository
            ->createQueryBuilder('x')
            ->setFirstResult($offset)
            ->setMaxResults(self::BULK)
        ;

        if ($id) {
            $queryBuilder->where('x.id = :id')->setParameter('id', $id);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Zone[] $zones
     */
    private function applyZones(ZoneableEntity $zoneable, array $zones): void
    {
        $zoneable->clearZones();
        foreach ($zones as $zone) {
            $zoneable->addZone($zone);
        }
    }
}

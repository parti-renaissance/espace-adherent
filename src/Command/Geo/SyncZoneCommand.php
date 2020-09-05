<?php

namespace App\Command\Geo;

use App\Command\Geo\Helper\Persister;
use App\Command\Geo\Helper\Summary;
use App\Entity\ConsularDistrict;
use App\Entity\ElectedRepresentative\Zone;
use App\Entity\ElectedRepresentative\ZoneCategory;
use App\Entity\ForeignDistrict;
use App\Entity\Geo\Canton;
use App\Entity\Geo\City;
use App\Entity\Geo\Country;
use App\Entity\Geo\Department;
use App\Entity\Geo\Region;
use App\Entity\ZoneInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SyncZoneCommand extends Command
{
    private const TYPES = [
        'country' => [
            'category_name' => ZoneCategory::COUNTRY,
            'entity_class' => Country::class,
        ],
        'region' => [
            'category_name' => ZoneCategory::REGION,
            'entity_class' => Region::class,
        ],
        'department' => [
            'category_name' => ZoneCategory::DEPARTMENT,
            'entity_class' => Department::class,
        ],
        'city' => [
            'category_name' => ZoneCategory::CITY,
            'entity_class' => City::class,
        ],
        'foreign-district' => [
            'category_name' => ZoneCategory::FOREIGN_DISTRICT,
            'entity_class' => ForeignDistrict::class,
        ],
        'consular-district' => [
            'category_name' => ZoneCategory::CONSULAR_DISTRICT,
            'entity_class' => ConsularDistrict::class,
        ],
    ];

    protected static $defaultName = 'app:geo:sync-zone';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var Collection
     */
    private $entities;

    /**
     * @var ZoneCategory[]
     */
    private $categoriesByName;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Create missing zone and link it to its parents')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Sync all zones (override "type" and "code" options).')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, sprintf('Zone category: %s', implode(', ', array_keys(self::TYPES))))
            ->addOption('code', null, InputOption::VALUE_REQUIRED, 'INSEE code.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm without persisting any data.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->entities = new ArrayCollection();

        $categories = $this->em->getRepository(ZoneCategory::class)->findAll();
        foreach ($categories as $category) {
            $this->categoriesByName[$category->getName()] = $category;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Start syncing zones');

        [
            'all' => $all,
            'type' => $type,
            'code' => $code,
        ] = $this->extractScope($input);

        $collectivities = $this->findCollectivities($all, $type, $code);

        $this->io->section('Transforming collectivities into zone');

        $this->io->progressStart(\count($collectivities));

        foreach ($collectivities as $collectivity) {
            $this->collectivityAsZone($collectivity);
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $summary = new Summary($this->io);
        $summary->run($this->entities);

        $persister = new Persister($this->io, $this->em);
        $persister->run($this->entities, $input->getOption('dry-run'));

        return 0;
    }

    private function extractScope(InputInterface $input): array
    {
        $all = (bool) $input->getOption('all');
        $type = $input->getOption('type');
        $code = $input->getOption('code');

        if ($all) {
            $type = null;
            $code = null;
        } else {
            if (!isset(self::TYPES[$type])) {
                throw new RuntimeException(sprintf('Invalid type "%s", expected values are: %s', $type, implode(', ', array_keys(self::TYPES))));
            }
        }

        return [
            'all' => $all,
            'type' => $type,
            'code' => $code,
        ];
    }

    /**
     * @return array|ZoneInterface[]
     */
    private function findCollectivities(bool $all, ?string $type, ?string $code): iterable
    {
        $this->io->section('Fetching collectivities');

        if ($code) {
            $this->io->comment(sprintf('Fetching "%s" code "%s" from database', $type, $code));

            return array_filter([
                $this->em
                    ->getRepository(self::TYPES[$type]['entity_class'])
                    ->findOneBy(['code' => $code]),
            ]);
        }

        $grouped = [];

        foreach (array_keys(self::TYPES) as $subType) {
            if (!$all && $subType !== $type) {
                continue;
            }

            $this->io->comment(sprintf('Fetching all "%s" from database', $subType));

            $entities = $this->em
                ->createQueryBuilder()
                ->select(['x'])
                ->from(self::TYPES[$subType]['entity_class'], 'x')
                ->getQuery()
                ->getResult()
            ;

            $this->io->comment(sprintf('Found %d "%s"', \count($entities), $subType));

            $grouped[] = $entities;
        }

        $entities = array_merge(...$grouped);

        $this->io->comment(sprintf('Total %d', \count($entities)));

        return $entities;
    }

    /**
     * @param Region|Department|City $collectivity
     */
    private function collectivityAsZone(object $collectivity): Zone
    {
        $category = $this->retrieveCategoryByCollectivity($collectivity);

        //
        // 1st attempt: local variable
        //

        $key = \get_class($category).'#'.$collectivity->getCode();
        if ($this->entities->containsKey($key)) {
            return $this->entities->get($key);
        }

        //
        // 2nd attempt: in database by category and code
        //

        $repository = $this->em->getRepository(Zone::class);
        $zone = $repository->findOneBy([
            'category' => $category,
            'code' => $collectivity->getCode(),
        ]);

        //
        // 3rd attempt: in database by category and name matching
        //

        if (!$zone) {
            $zone = $this->guessZoneByCollectivityName($collectivity);
        }

        //
        // Creates a fresh entity if it's necessary
        //

        if (!$zone) {
            $zone = new Zone($category, $collectivity->getName());
        }

        //
        // Setup entity
        //

        $this->entities->set($key, $zone);

        $zone->setName(sprintf('%s (%s)', $collectivity->getName(), $collectivity->getCode()));
        $zone->setCode($collectivity->getCode());

        $zone->getParents()->clear();
        foreach ($collectivity->getParents() as $collectivityParent) {
            // Skips some geo levels level
            if (
                $collectivityParent instanceof Country ||
                $collectivityParent instanceof Canton
            ) {
                continue;
            }

            $zone->getParents()->add($this->collectivityAsZone($collectivityParent));
        }

        return $zone;
    }

    /**
     * "<name>"
     * "<name> (<insee>)"
     * "<name> (<postal code>)" // n-times, if $collectivity is a cities
     */
    private function guessZoneByCollectivityName(ZoneInterface $collectivity): ?Zone
    {
        $category = $this->retrieveCategoryByCollectivity($collectivity);

        $names = [
            $collectivity->getName(),
            sprintf('%s (%s)', $collectivity->getName(), $collectivity->getCode()),
        ];

        if ($collectivity instanceof City) {
            foreach ($collectivity->getPostalCode() as $postalCode) {
                $names[] = sprintf('%s (%s)', $collectivity->getName(), $postalCode);
            }
        }

        $repository = $this->em->getRepository(Zone::class);

        /* @var QueryBuilder $qb */
        $qb = $repository->createQueryBuilder('z');
        $nameExpr = $qb->expr()->orX();
        foreach (array_keys($names) as $key) {
            $nameExpr->add($qb->expr()->like('z.name', "?$key"));
        }

        $zones = $qb
            ->andWhere($qb->expr()->eq('z.category', ':category'))
            ->andWhere($nameExpr)
            ->setParameters(array_merge(['category' => $category], $names))
            ->getQuery()
            ->getResult()
        ;

        if (\count($zones) > 1) {
            throw new RuntimeException(sprintf("Can't determine zone for entity %s (%s), %d zones found", $collectivity->getName(), $collectivity->getCode(), \count($zones)));
        }

        return reset($zones) ?: null;
    }

    /**
     * @param ZoneInterface $collectivity
     */
    private function retrieveCategoryByCollectivity(object $collectivity): ZoneCategory
    {
        foreach (self::TYPES as $data) {
            if ($collectivity instanceof $data['entity_class']) {
                if (isset($this->categoriesByName[$data['category_name']])) {
                    return $this->categoriesByName[$data['category_name']];
                }

                break;
            }
        }

        throw new \InvalidArgumentException(sprintf('No category found for class %s', \get_class($collectivity)));
    }
}

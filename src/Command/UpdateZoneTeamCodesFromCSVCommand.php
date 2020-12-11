<?php

namespace App\Command;

use App\Command\ChezVous\AbstractImportCommand;
use App\Entity\Geo\Zone;
use App\Repository\ChezVous\CityRepository;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateZoneTeamCodesFromCSVCommand extends AbstractImportCommand
{
    protected static $defaultName = 'app:geo-zones:add-team-code';

    protected const BATCH_SIZE = 10;

    /** @var ZoneRepository */
    private $zoneRepository;

    public function __construct(
        EntityManagerInterface $em,
        CityRepository $cityRepository,
        FilesystemInterface $storage,
        ZoneRepository $zoneRepository
    ) {
        parent::__construct($em, $cityRepository, $storage);

        $this->em = $em;
        $this->zoneRepository = $zoneRepository;
    }

    protected function configure()
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, 'Filename of the CSV file team codes and zone information')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('path');

        if (!$this->storage->has($filePath)) {
            $this->io->comment("No CSV found ($filePath).");

            return;
        }

        $this->io->text('Start updating team code of zones');
        $this->io->text("Processing \"$filePath\"");

        $reader = $this->createReader($filePath);

        $this->io->progressStart($reader->count());

        $row = $reader->fetchOne();
        if (!isset($row['team_code'])) {
            $this->io->error('Impossible to update team codes: file does not contains a column "team_code".');

            return;
        }

        foreach ($reader as $index => $row) {
            /** @var Zone $zone */
            if (!$zone = $this->zoneRepository->findOneBy(['code' => $row['code'], 'type' => $row['type']])) {
                $this->io->warning(\sprintf('Zone with code "%s" and type "%s" does not exist.', $row['code'], $row['type']));

                continue;
            }

            $zone->setTeamCode('' !== $row['team_code'] ? $row['team_code'] : null);

            if ($index > 0 && 0 === ($index % self::BATCH_SIZE)) {
                $this->io->progressAdvance(self::BATCH_SIZE);
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->io->progressFinish();
        $this->em->flush();

        $this->io->writeln('');
        $this->io->success('Done');
    }
}

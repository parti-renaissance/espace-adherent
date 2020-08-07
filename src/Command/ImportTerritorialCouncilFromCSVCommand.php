<?php

namespace App\Command;

use App\Command\ChezVous\AbstractImportCommand;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Repository\ChezVous\CityRepository;
use App\Repository\ReferentTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportTerritorialCouncilFromCSVCommand extends AbstractImportCommand
{
    protected static $defaultName = 'app:territorial-council:import-from-csv';

    protected const BATCH_SIZE = 10;

    /** @var ReferentTagRepository */
    private $referentTagRepository;

    public function __construct(
        EntityManagerInterface $em,
        CityRepository $cityRepository,
        Filesystem $storage,
        ReferentTagRepository $referentTagRepository
    ) {
        parent::__construct($em, $cityRepository, $storage);

        $this->em = $em;
        $this->referentTagRepository = $referentTagRepository;
    }

    protected function configure()
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, 'Filename of the CSV file with territorial councils')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('path');

        if (!$this->storage->has($filePath)) {
            $this->io->comment("No CSV found ($filePath).");

            return;
        }

        $this->io->text('Start importing territorial councils');
        $this->io->text("Processing \"$filePath\"");

        $reader = $this->createReader($filePath);

        $this->io->progressStart($reader->count());

        foreach ($reader as $index => $row) {
            if (null == $row['name']) {
                $this->io->warning('Territorial council without name cannot be added.');

                continue;
            }

            /** @var TerritorialCouncil $territorialCouncil */
            if ($territorialCouncil = $this->em->getRepository(TerritorialCouncil::class)->findOneBy(['name' => $row['name']])) {
                $territorialCouncil->clearReferentTags();
            } else {
                $territorialCouncil = new TerritorialCouncil(trim($row['name']), trim($row['code']));
            }

            $tags = explode(',', $row['referent_tag.code']);

            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!($referentTag = $this->referentTagRepository->findOneByCode($tag))) {
                    $this->io->warning(sprintf('ReferentTag with code (%s) has not been found.', $tag));
                } else {
                    $territorialCouncil->addReferentTag($referentTag);
                }
            }

            $this->em->persist($territorialCouncil);

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

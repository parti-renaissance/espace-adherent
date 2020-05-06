<?php

namespace App\Command;

use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ImportAssessorsCommand extends Command
{
    protected static $defaultName = 'app:assessors:import';

    private $em;
    private $repository;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $em, AdherentRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'CSV file of all assessors to load');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->text('Start importing assessors');

        $assessors = $this->parseCSV($input->getArgument('file'));

        $this->io->progressStart(\count($assessors));

        $i = 0;
        $batchSize = 50;
        foreach ($assessors as $assessor) {
            ++$i;

            if (!$adherent = $this->repository->findOneByEmail($assessor['email'])) {
                $this->io->warning(sprintf('Adherent with email (%s) not found.', $assessor['email']));

                continue;
            }

            $adherent->setAssessorManagedAreaCodesAsString($assessor['zone']);

            if (0 === ($i % $batchSize)) {
                $this->io->progressAdvance($batchSize);
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->io->progressFinish();
        $this->em->flush();
        $this->em->clear();

        $this->io->writeln('');
        $this->io->success('Done');
    }

    private function parseCSV(string $filename): array
    {
        $rows = [];
        if (false === ($handle = @fopen($filename, 'r'))) {
            throw new FileNotFoundException(sprintf('%s not found', $filename));
        }

        $firstline = true;

        while (false !== ($data = fgetcsv($handle, 10000, ','))) {
            if ($firstline) {
                $firstline = false;

                continue;
            }

            $row = array_map('trim', $data);
            $rows[] = [
                'email' => $row[0],
                'zone' => $row[1],
            ];
        }
        fclose($handle);

        return $rows;
    }
}

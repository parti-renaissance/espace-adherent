<?php

namespace App\Command;

use App\Entity\Donation;
use App\Geocoder\Coordinates;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ImportDonationCoordinatesCommand extends Command
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:donations:import-coordinates')
            ->addArgument('filename', InputArgument::REQUIRED)
            ->setDescription('Import Donation coordinates')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['', 'Starting import of Donation coordinates.']);

        $donations = $this->parseCSV($input->getArgument('filename'));
        $progress = new ProgressBar($output, \count($donations));

        $count = 0;
        foreach ($donations as $index => $row) {
            list($uuid, $latitude, $longitude) = $row;

            if (empty($uuid)) {
                throw new \RuntimeException(sprintf('No uuid found for Donation. (line %d)', $index + 1));
            }

            if (!$donation = $this->findDonation($uuid)) {
                throw new \RuntimeException(sprintf('No Donation found with uuid "%s". (line %d)', $uuid, $index + 1));
            }

            if ($donation->getLatitude() || $donation->getLongitude()) {
                continue;
            }

            if (empty($latitude) || empty($longitude)) {
                throw new \RuntimeException(sprintf('No coordinates found for Donation "%s". (line %d)', $uuid, $index + 1));
            }

            $donation->updateCoordinates(new Coordinates($latitude, $longitude));

            ++$count;

            if (0 === ($count % 250)) {
                $progress->advance(250);

                $this->em->flush();
                $this->em->clear();
            }
        }

        $progress->finish();

        $this->em->flush();
        $this->em->clear();

        $output->writeln('Imported Donation coordinates successfully.');
    }

    private function parseCSV(string $filepath): array
    {
        if (false === ($handle = fopen($filepath, 'r'))) {
            throw new FileNotFoundException(sprintf('File "%s" was not found', $filename));
        }

        $isFirstRow = true;
        while (false !== ($data = fgetcsv($handle, 0, ','))) {
            if (true === $isFirstRow) {
                $isFirstRow = false;

                continue;
            }

            $rows[] = array_map('trim', $data);
        }

        fclose($handle);

        return $rows ?? [];
    }

    private function findDonation(string $uuid): ?Donation
    {
        return $this->em->getRepository(Donation::class)->findOneByUuid($uuid);
    }
}

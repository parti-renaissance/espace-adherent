<?php

namespace AppBundle\Command;

use AppBundle\Entity\LegislativeCandidate;
use AppBundle\Entity\LegislativeDistrictZone;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LegislativesLoadDistrictZonesCommand extends ContainerAwareCommand
{
    private const DISTRICTS_TOTAL = 577;

    private const RANK = 0;
    private const AREA_CODE = 1;
    private const NAME = 2;

    private const DISTRICT_NUMBER = 3;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $manager;

    protected function configure()
    {
        $this
            ->setName('app:legislatives:load-district-zones')
            ->addArgument('csv-file', InputArgument::REQUIRED, 'CSV to load containing [code_circonscription, department, numero, communes]. Can be found on https://www.data.gouv.fr/fr/datasets/countours-des-circonscriptions-des-legislatives-nd/')
            ->addOption('csv-delimiter', 'd', InputOption::VALUE_OPTIONAL, 'Set the field delimiter (one character only)', ',')
            ->setDescription('Create Legislatives District Zones from a CSV file, and add a default Legislative Candidate.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $csvPath = $input->getArgument('csv-file');

        $fs = $this->getContainer()->get('filesystem');
        if (!$fs->exists($csvPath)) {
            throw new \RuntimeException(sprintf('File "%s" does not exists.', $csvPath));
        }

        if (strlen($input->getOption('csv-delimiter')) !== 1) {
            throw new \RuntimeException('CSV delimiter must be one character only.');
        }

        $this->manager = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $firstLine = false;
        $districts = [];
        $progress = new ProgressBar($output, self::DISTRICTS_TOTAL);

        $handle = fopen($input->getArgument('csv-file'), 'r');
        while (($data = fgetcsv($handle, 1000, $input->getOption('csv-delimiter'))) !== false) {
            if (count($data) != 6) {
                continue;
            }
            if (!$firstLine) {
                $firstLine = true;
                continue;
            }

            if (!isset($districts[$data[self::AREA_CODE]])) {
                $district = $this->manager->getRepository(LegislativeDistrictZone::class)
                    ->findOneByAreaCode($data[self::AREA_CODE]);
                if (!$district) {
                    $create = intval($data[self::AREA_CODE]) < 1000 ? 'createDepartmentZone' : 'createRegionZone';
                    $district = LegislativeDistrictZone::{$create}(
                        $data[self::AREA_CODE],
                        $data[self::NAME],
                        [],
                        $data[self::RANK]
                    );
                    $this->manager->persist($district);
                } else {
                    /* @var LegislativeDistrictZone $district */
                    $district->setName($data[self::NAME]);
                    $district->setRank($data[self::RANK]);
                    $district->setAreaCode($data[self::AREA_CODE]);
                }
                $districts[$data[self::AREA_CODE]] = $district;
            } else {
                $district = $districts[$data[self::AREA_CODE]];
            }

            $candidate = $this->manager->getRepository(LegislativeCandidate::class)->findOneBy([
                'districtNumber' => $data[self::DISTRICT_NUMBER],
                'districtZone' => $district,
            ]);
            if (!$candidate) {
                $candidate = new LegislativeCandidate();
                $this->manager->persist($candidate);
                $candidate->setDistrictZone($district);
                $candidate->setDistrictNumber($data[self::DISTRICT_NUMBER]);
                $candidate->setSlug(sprintf(
                    'circonscription-%d-du-%s',
                    $data[self::DISTRICT_NUMBER],
                    $data[self::AREA_CODE]
                ));
            }
            $candidate->setFirstName('Le candidat En Marche à cette circonscription');
            $candidate->setLastName('n\'est pas encore public');
            $candidate->setDistrictName($data[self::NAME]);
            $candidate->setGender('-');
            $candidate->setCareer('-');

            $progress->advance();
        }
        fclose($handle);
        $this->manager->flush();

        if ($progress->getProgress() != $progress->getMaxSteps()) {
            (new SymfonyStyle($input, $output))->warning(sprintf(
                '%d districts loaded instead of %d.',
                $progress->getProgress(),
                $progress->getMaxSteps()
            ));
        } else {
            $progress->finish();
            $output->writeln('');
        }
    }
}

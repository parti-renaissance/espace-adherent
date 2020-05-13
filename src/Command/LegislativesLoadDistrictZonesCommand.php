<?php

namespace App\Command;

use App\Entity\LegislativeCandidate;
use App\Entity\LegislativeDistrictZone;
use Doctrine\Common\Persistence\ObjectManager;
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

    private const AREA_CODE = 0;
    private const NAME = 1;
    private const DISTRICT_NUMBER = 2;
    private const DISTRICT_LABEL = 3;

    /**
     * @var ObjectManager
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
        $fs = $this->getContainer()->get('filesystem');
        if (!$fs->exists($csvPath = $input->getArgument('csv-file'))) {
            throw new \RuntimeException(sprintf('File "%s" does not exists.', $csvPath));
        }

        if (1 !== \strlen($input->getOption('csv-delimiter'))) {
            throw new \RuntimeException('CSV delimiter must be one character only.');
        }

        $this->manager = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $firstLine = false;
        $districts = [];
        $progress = new ProgressBar($output, self::DISTRICTS_TOTAL);

        $handle = fopen($input->getArgument('csv-file'), 'rb');
        while (false !== ($data = fgetcsv($handle, 1000, $input->getOption('csv-delimiter')))) {
            if (5 !== \count($data)) {
                continue;
            }

            if (!$firstLine) {
                $firstLine = true;

                continue;
            }

            $areaCode = LegislativeDistrictZone::normalizeAreaCode($data[self::AREA_CODE]);

            if (!isset($districts[$areaCode])) {
                $district = $this->manager->getRepository(LegislativeDistrictZone::class)->findDistrictZone($areaCode);
                if (!$district) {
                    $this->manager->persist($district = $this->createDistrictZone($areaCode, $data[self::NAME]));
                } else {
                    $district->setName($data[self::NAME]);
                    $district->setAreaCode($areaCode);
                }
                $districts[$areaCode] = $district;
            } else {
                $district = $districts[$areaCode];
            }

            $areaNumber = $data[self::DISTRICT_NUMBER];

            if (!$this->manager->getRepository(LegislativeCandidate::class)->findDistrictZoneCandidate($areaCode, $areaNumber)) {
                $this->manager->persist($this->createCandidate($district, $areaNumber, $data));
            }

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

    private function createCandidate(
        LegislativeDistrictZone $district,
        string $areaNumber,
        array $data
    ): LegislativeCandidate {
        $slugifier = $this->getContainer()->get('sonata.core.slugify.cocur');

        $position = ((int) $district->getZoneNumber()) * 100 + ((int) $areaNumber);
        if (!is_numeric($district->getZoneNumber())) {
            $position = 20 * 100 + ((int) $areaNumber);
        }

        $candidate = new LegislativeCandidate();
        $candidate->setPosition($position);
        $candidate->setDistrictZone($district);
        $candidate->setDistrictNumber($areaNumber);
        $candidate->setDistrictName($data[self::NAME].' - '.$data[self::DISTRICT_LABEL]);
        $candidate->setFirstName('Bientôt annoncé(e)');
        $candidate->setLastName('');
        $candidate->setDescription('Notre candidat(e) dans cette circonscription sera prochainement annoncé(e).');
        $candidate->setGender('-');
        $candidate->setCareer('-');
        $candidate->setSlug($slugifier->slugify($data[self::NAME].' - '.$data[self::DISTRICT_LABEL]));

        return $candidate;
    }

    private function createDistrictZone(string $areaCode, string $name): LegislativeDistrictZone
    {
        if ('0' === $areaCode[0]) {
            return LegislativeDistrictZone::createDepartmentZone($areaCode, $name);
        }

        return LegislativeDistrictZone::createRegionZone($areaCode, $name);
    }
}

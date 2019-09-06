<?php

namespace AppBundle\Command\ChezVous;

use AppBundle\DataFixtures\ORM\LoadChezVousMeasureTypeData;
use AppBundle\Entity\ChezVous\MeasureType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportMeasureTypesCommand extends Command
{
    protected static $defaultName = 'app:chez-vous:import-measure-types';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    protected function configure()
    {
        $this->setDescription('Import ChezVous measure types');
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('ChezVous measure types import.');

        $this->em->beginTransaction();

        $this->importMeasureTypes();

        $this->em->commit();

        $this->io->success('ChezVous measure types imported successfully!');
    }

    private function importMeasureTypes(): void
    {
        foreach (LoadChezVousMeasureTypeData::TYPES as $type) {
            $measureType = new MeasureType($type['code'], $type['label']);
            $measureType->setSourceLabel($type['sourceLabel']);
            $measureType->setSourceLink($type['sourceLink']);
            $measureType->setOldolfLink($type['oldolfLink']);
            $measureType->setEligibilityLink($type['eligibilityLink']);
            $measureType->setCitizenProjectsLink('https://en-marche.fr/projets-citoyens');
            $measureType->setIdeasWorkshopLink('https://en-marche.fr/atelier-des-idees/proposer');

            $this->em->persist($measureType);
        }

        $this->em->flush();
    }
}

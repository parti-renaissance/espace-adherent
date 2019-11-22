<?php

namespace AppBundle\Command;

use AppBundle\Entity\ProgrammaticFoundation\Approach;
use AppBundle\Entity\ProgrammaticFoundation\Measure;
use AppBundle\Entity\ProgrammaticFoundation\Project;
use AppBundle\Entity\ProgrammaticFoundation\SubApproach;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportProgrammaticFoundationCommand extends Command
{
    protected static $defaultName = 'app:programmatic-foundation:import';

    private $storage;
    private $em;

    /**
     * @var SymfonyStyle|null
     */
    private $io;

    public function __construct(FilesystemInterface $storage, EntityManagerInterface $em)
    {
        $this->storage = $storage;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('filename', InputArgument::REQUIRED)
            ->setDescription('Import Programmatic Foundation')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->section('Starting programmatic foundation import.');

        $csv = Reader::createFromStream($this->storage->readStream($input->getArgument('filename')));
        $csv->setHeaderOffset(0);

        $this->io->progressStart($total = $csv->count());

        $line = 0;
        $previousApproachTitle = $previousSubApproachTitle = $previousMeasureTitle = null;
        $approachPosition = $subApproachPosition = $measurePosition = 1;

        foreach ($csv as $row) {
            $approachTitle = mb_substr(reset($row), 0, 255);
            $subApproachTitle = mb_substr(next($row), 0, 255);
            $measureTitle = mb_substr(next($row), 0, 255);
            $measureContent = next($row);
            $project1Title = mb_substr(next($row), 0, 255);
            $project1Content = next($row);
            $project1City = mb_substr(next($row), 0, 255);
            $project2Title = mb_substr(next($row), 0, 255);
            $project2Content = next($row);
            $project2City = mb_substr(next($row), 0, 255);

            if (!empty($approachTitle) && $previousApproachTitle !== $approachTitle) {
                $approach = $this->createApproach($approachPosition, $approachTitle);
                ++$approachPosition;
                $subApproachPosition = 0;

                $this->em->persist($approach);
                $this->em->flush();
            }

            if (!empty($subApproachTitle) && $previousSubApproachTitle !== $subApproachTitle) {
                $subApproach = $this->createSubApproach($subApproachPosition, $subApproachTitle);
                $approach->addSubApproach($subApproach);
                ++$subApproachPosition;
                $measurePosition = 0;

                $this->em->persist($subApproach);
                $this->em->flush();
            }

            if (!empty($measureTitle)) {
                $measure = $this->createMeasure($measurePosition, $measureTitle, $measureContent);
                $subApproach->addMeasure($measure);
                ++$measurePosition;

                $this->em->persist($measure);
                $this->em->flush();
            }

            $this->addProject($measure, 1, $project1Title, $project1Content, $project1City);
            $this->addProject($measure, 2, $project2Title, $project2Content, $project2City);

            ++$line;

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success('Programmatic foundation imported successfully!');
    }

    private function addProject(Measure $measure, int $position, ?string $title, ?string $content, ?string $city): void
    {
        if (empty($title)) {
            return;
        }

        $project = $this->createProject(1, $title, $content, $city);
        $measure->addProject($project);

        $this->em->persist($project);
        $this->em->flush();
    }

    private function createApproach(int $position, string $title): Approach
    {
        return new Approach($position, $title);
    }

    private function createSubApproach(int $position, string $title): SubApproach
    {
        return new SubApproach($position, $title);
    }

    private function createMeasure(int $position, string $title, string $content): Measure
    {
        return new Measure($position, $title, $content);
    }

    private function createProject(int $position, string $title, string $content, string $city): Project
    {
        return new Project($position, $title, $content, $city);
    }
}

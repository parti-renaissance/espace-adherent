<?php

namespace AppBundle\Command;

use AppBundle\Entity\ProgrammaticFoundation\Approach;
use AppBundle\Entity\ProgrammaticFoundation\Measure;
use AppBundle\Entity\ProgrammaticFoundation\Project;
use AppBundle\Entity\ProgrammaticFoundation\SubApproach;
use AppBundle\Entity\ProgrammaticFoundation\Tag;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportProgrammaticFoundationCommand extends Command
{
    protected static $defaultName = 'app:programmatic-foundation:import';

    private $storage;
    private $em;

    private $validationErrors = [];

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
            ->addOption(
                'append',
                null,
                InputOption::VALUE_NONE,
                'If defined, no reset of the database will be made before import.'
            )
            ->addOption(
                'skip-errors',
                null,
                InputOption::VALUE_NONE,
                'If defined, errors are skipped instead of canceling to whole import.'
            )
            ->setDescription('Import Programmatic Foundation')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em->beginTransaction();

        try {
            $this->import($input);

            if (0 < \count($this->validationErrors)) {
                foreach ($this->validationErrors as $validationError) {
                    $this->io->comment($validationError);
                }
            }

            if (false !== $input->getOption('skip-errors') || 0 === \count($this->validationErrors)) {
                $this->em->commit();

                $this->io->success('Programmatic foundation imported successfully!');
            } else {
                $this->em->rollback();

                $this->io->error(sprintf('Import canceled due to %d validation errors.', \count($this->validationErrors)));
            }
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }
    }

    private function import(InputInterface $input): void
    {
        if (false === $input->getOption('append')) {
            $this->io->section('Resetting programmatic foundation.');

            $approaches = $this->em->getRepository(Approach::class)->findAll();

            foreach ($approaches as $approach) {
                $this->em->remove($approach);
            }

            $tags = $this->em->getRepository(Tag::class)->findAll();

            foreach ($tags as $tag) {
                $this->em->remove($tag);
            }

            $this->em->flush();
        }

        $this->io->section('Starting programmatic foundation import.');

        $csv = Reader::createFromStream($this->storage->readStream($input->getArgument('filename')));
        $csv->setHeaderOffset(0);

        $this->io->progressStart($total = $csv->count());

        $line = 0;
        $approach = $subApproach = null;
        $previousApproachTitle = $previousSubApproachTitle = null;
        $approachPosition = $subApproachPosition = $measurePosition = 1;

        foreach ($csv as $row) {
            $approachTitle = mb_substr(trim(reset($row)), 0, 255);
            $subApproachTitle = mb_substr(trim(next($row)), 0, 255);
            $measureTitle = mb_substr(trim(next($row)), 0, 255);
            $measureContent = trim(next($row));
            $measureTags = trim(next($row));

            if (empty($measureTitle)) {
                continue;
            }

            if (empty($measureContent)) {
                $this->validationErrors[] = sprintf('Measure with title "%s" has no content. (line %d)', $measureTitle, $line);

                continue;
            }

            if (!empty($approachTitle) && $previousApproachTitle !== $approachTitle) {
                if (!$approach = $this->findApproach($approachTitle)) {
                    $approach = $this->createApproach($approachPosition, $approachTitle);
                    ++$approachPosition;
                    $subApproachPosition = 1;

                    $this->em->persist($approach);
                    $this->em->flush();
                }

                $previousApproachTitle = $approachTitle;
            }

            if (!$approach) {
                continue;
            }

            if (!empty($subApproachTitle) && $previousSubApproachTitle !== $subApproachTitle) {
                if (!$subApproach = $this->findSubApproach($subApproachTitle)) {
                    $subApproach = $this->createSubApproach($subApproachPosition, $subApproachTitle);
                    $approach->addSubApproach($subApproach);
                    ++$subApproachPosition;
                    $measurePosition = 1;

                    $this->em->persist($subApproach);
                    $this->em->flush();
                }

                $previousSubApproachTitle = $subApproachTitle;
            }

            if (!$subApproach) {
                continue;
            }

            $measure = $this->createMeasure($measurePosition, $measureTitle, $measureContent, $measureTags);
            $subApproach->addMeasure($measure);
            ++$measurePosition;

            $this->em->persist($measure);
            $this->em->flush();

            for ($i = 1; $i <= 8; ++$i) {
                $projectTitle = trim(next($row));
                $projectContent = trim(next($row));
                $projectCity = trim(next($row));
                $projectTags = trim(next($row));

                if (empty($projectTitle)) {
                    continue;
                }

                if (empty($projectContent)) {
                    $this->validationErrors[] = sprintf('Project with title "%s" has no content. (line %d)', $projectTitle, $line);

                    continue;
                }

                if (empty($projectCity)) {
                    $this->validationErrors[] = sprintf('Project with title "%s" has no city. (line %d)', $projectTitle, $line);

                    continue;
                }

                $this->addProject(
                    $measure,
                    $i,
                    mb_substr($projectTitle, 0, 255),
                    $projectContent,
                    mb_substr($projectCity, 0, 255),
                    $projectTags
                );
            }

            ++$line;

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
    }

    private function addProject(
        Measure $measure,
        int $position,
        ?string $title,
        string $content,
        ?string $city,
        ?string $tags
    ): void {
        $project = $this->createProject($position, $title, $content, $city, $tags);
        $measure->addProject($project);

        $this->em->persist($project);
        $this->em->flush();
    }

    private function findApproach(string $title): ?Approach
    {
        return $this->em->getRepository(Approach::class)->findOneBy(['title' => $title]);
    }

    private function findSubApproach(string $title): ?SubApproach
    {
        return $this->em->getRepository(SubApproach::class)->findOneBy(['title' => $title]);
    }

    private function createApproach(int $position, string $title): Approach
    {
        return new Approach($position, $title);
    }

    private function createSubApproach(int $position, string $title): SubApproach
    {
        return new SubApproach($position, $title);
    }

    private function createMeasure(int $position, string $title, string $content, ?string $tags): Measure
    {
        $measure = new Measure($position, $title, $content);

        $tagsArray = explode(',', $tags);

        foreach ($tagsArray as $tagLabel) {
            $tagLabel = trim($tagLabel);

            if (empty($tagLabel)) {
                continue;
            }

            if (!$tag = $this->findTag($tagLabel)) {
                $tag = new Tag($tagLabel);

                $this->em->persist($tag);
            }

            $measure->addTag($tag);
        }

        return $measure;
    }

    private function createProject(int $position, string $title, string $content, string $city, ?string $tags): Project
    {
        $project = new Project($position, $title, $content, $city);

        $tagsArray = explode(',', $tags);
        foreach ($tagsArray as $tagLabel) {
            $tagLabel = trim($tagLabel);

            if (empty($tagLabel)) {
                continue;
            }

            if (!$tag = $this->findTag($tagLabel)) {
                $tag = new Tag($tagLabel);

                $this->em->persist($tag);
            }

            $project->addTag($tag);
        }

        return $project;
    }

    private function findTag(string $tagLabel): ?Tag
    {
        return $this->em->getRepository(Tag::class)->findOneBy(['label' => $tagLabel]);
    }
}

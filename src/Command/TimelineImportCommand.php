<?php

namespace AppBundle\Command;

use AppBundle\Entity\Timeline\Manifesto;
use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\MeasureTranslation;
use AppBundle\Entity\Timeline\Profile;
use AppBundle\Entity\Timeline\Theme;
use AppBundle\Timeline\TimelineFactory;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TimelineImportCommand extends Command
{
    private const BOOLEAN_CHOICES = ['oui' => true, 'non' => false];
    private const CSV_DIRECTORY = 'timeline';
    private const CSV_PROFILES = 'profiles.csv';
    private const CSV_THEMES = 'themes.csv';
    private const CSV_MEASURES = 'measures';

    private $em;
    private $factory;
    private $storage;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $em, TimelineFactory $factory, Filesystem $storage)
    {
        $this->em = $em;
        $this->factory = $factory;
        $this->storage = $storage;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:timeline:import')
            ->setDescription('Import timeline from CSV files')
            ->addArgument('manifestoSlug', InputArgument::REQUIRED, 'The manifesto slug to link measures with.');
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Import timeline measures');

        $this->em->beginTransaction();

        $this->importProfiles();
        $this->importThemes();
        $this->importMeasures($input->getArgument('manifestoSlug'));

        $this->em->commit();

        $output->writeln(['', 'Timeline imported successfully!']);
    }

    private function importProfiles(): void
    {
        $this->io->section('Importing profiles');

        $filename = self::CSV_DIRECTORY.'/'.self::CSV_PROFILES;

        if (!$this->storage->has($filename)) {
            $this->io->comment("No CSV found ($filename).");

            return;
        }

        $this->io->comment("Processing \"$filename\".");

        $reader = Reader::createFromStream($this->storage->readStream($filename));
        $reader->setHeaderOffset(0);

        $this->io->progressStart($total = $reader->count());

        $count = 0;
        foreach ($reader as $index => $row) {
            $title = $row['title'];
            $description = $row['description'];

            if (empty($title)) {
                throw new \RuntimeException(sprintf('No title found for profile. (line %d)', $index + 2));
            }

            if (empty($description)) {
                throw new \RuntimeException(sprintf('No description found for profile "%s". (line %d)', $title, $index + 2));
            }

            $this->em->persist($this->factory->createProfile($title, $description));

            ++$count;
        }

        $this->em->flush();
        $this->em->clear();

        $this->io->progressFinish();

        $this->io->comment("Processed $total profiles.");
    }

    private function importThemes(): void
    {
        $this->io->section('Importing themes');

        $filename = self::CSV_DIRECTORY.'/'.self::CSV_THEMES;

        if (!$this->storage->has($filename)) {
            $this->io->comment("No CSV found ($filename).");

            return;
        }

        $this->io->comment("Processing \"$filename\".");

        $reader = Reader::createFromStream($this->storage->readStream($filename));
        $reader->setHeaderOffset(0);

        $this->io->progressStart($total = $reader->count());

        $count = 0;
        foreach ($reader as $index => $row) {
            $title = $row['title'];
            $isFeatured = $row['is_featured'];
            $description = $row['description'];
            $imageUrl = $row['image_url'];

            if (empty($title)) {
                throw new \RuntimeException(sprintf('No title found for theme. (line %d)', $index + 2));
            }

            $isFeatured = strtolower($isFeatured);
            if (!array_key_exists($isFeatured, self::BOOLEAN_CHOICES)) {
                throw new \RuntimeException(sprintf(
                    'Invalid featured flag label "%s" given for theme "%s". Valid values are: "%s". (line %d)',
                    $isFeatured,
                    $title,
                    implode(', ', array_keys(self::BOOLEAN_CHOICES)),
                    $index + 2
                ));
            }

            if (empty($description)) {
                throw new \RuntimeException(sprintf('No description found for theme "%s". (line %d)', $title, $index + 2));
            }

            if (empty($imageUrl)) {
                throw new \RuntimeException(sprintf('No image url found for theme "%s". (line %d)', $title, $index + 2));
            }

            $this->em->persist($this->factory->createTheme(
                $title,
                $description,
                $imageUrl,
                self::BOOLEAN_CHOICES[$isFeatured]
            ));

            ++$count;
        }

        $this->em->flush();
        $this->em->clear();

        $this->io->comment("Processed $total themes.");
    }

    public function importMeasures(string $manifestoSlug): void
    {
        $this->io->section('Importing measures');

        $savedProfiles = $this->getProfiles();
        $savedThemes = $this->getThemes();
        $manifesto = $this->getManifesto($manifestoSlug);

        $filename = sprintf('%s/%s_%s.csv', self::CSV_DIRECTORY, self::CSV_MEASURES, $manifestoSlug);

        if (!$this->storage->has($filename)) {
            $this->io->comment("No CSV found ($filename).");

            return;
        }

        $this->io->comment("Processing \"$filename\".");

        $reader = Reader::createFromStream($this->storage->readStream($filename));
        $reader->setHeaderOffset(0);

        $this->io->progressStart($total = $reader->count());

        $count = 0;
        foreach ($reader as $index => $row) {
            $title = $row['title'];
            $status = $row['status'];
            $isGlobal = $row['is_global'];
            $themes = $row['themes'];
            $profiles = $row['profiles'];
            $link = $row['link'];

            if (empty($title)) {
                throw new \RuntimeException(sprintf('No title found for measure. (line %d)', $index + 2));
            }

            if (Measure::TITLE_MAX_LENGTH < mb_strlen($title)) {
                throw new \RuntimeException(sprintf(
                    'Measure title "%s" is too long. (%d characters max).',
                    $title,
                    Measure::TITLE_MAX_LENGTH
                ));

                continue;
            }

            if (!in_array($status, Measure::STATUSES, true)) {
                throw new \RuntimeException(sprintf(
                    'Invalid status for measure "%s": "%s" given, valid values are "%s". (line %d)',
                    $title,
                    $status,
                    implode(', ', array_keys(Measure::STATUSES)),
                    $index + 2
                ));
            }

            $relatedThemes = [];
            if (!empty($themes)) {
                foreach (explode(',', $themes) as $themeTitle) {
                    $themeTitle = trim($themeTitle);

                    if (!array_key_exists($themeTitle, $savedThemes)) {
                        throw new \RuntimeException(sprintf(
                            'No theme found with title "%s" for measure "%s". (line %d)',
                            $themeTitle,
                            $title,
                            $index + 2
                        ));
                    }

                    $relatedThemes[] = $savedThemes[$themeTitle];
                }
            }

            $relatedProfiles = [];
            if (!empty($profiles)) {
                foreach (explode(',', $profiles) as $profileTitle) {
                    $profileTitle = trim($profileTitle);

                    if (!array_key_exists($profileTitle, $savedProfiles)) {
                        throw new \RuntimeException(sprintf(
                            'No profile found with title "%s" for measure "%s". (line %d)',
                            $profileTitle,
                            $title,
                            $index + 2
                        ));
                    }

                    $relatedProfiles[] = $savedProfiles[$profileTitle];
                }
            }

            $measure = new Measure(
                $status,
                $relatedProfiles,
                $relatedThemes,
                $manifesto,
                $link,
                !empty($isGlobal)
            );

            $measure->addTranslation(new MeasureTranslation('fr', $title));

            $this->em->persist($measure);

            $this->io->progressAdvance();
            ++$count;

            if (0 === ($count % 50)) {
                $this->em->flush();
                $this->em->clear(Measure::class);
                $this->em->clear(MeasureTranslation::class);

                $this->io->comment("Processed $count measures.");
            }
        }

        $this->em->flush();
        $this->em->clear();

        $this->io->comment("Processed $total measures.");
    }

    private function getProfiles(): array
    {
        /** @var Profile $profile */
        foreach ($this->em->getRepository(Profile::class)->findAll() as $profile) {
            $profiles[$profile->translate()->getTitle()] = $profile;
        }

        return $profiles ?? [];
    }

    private function getThemes(): array
    {
        /** @var Theme $theme */
        foreach ($this->em->getRepository(Theme::class)->findAll() as $theme) {
            $themes[$theme->translate()->getTitle()] = $theme;
        }

        return $themes ?? [];
    }

    private function getManifesto(string $slug): ?Manifesto
    {
        return $this->em->getRepository(Manifesto::class)->findOneBySlug($slug);
    }
}

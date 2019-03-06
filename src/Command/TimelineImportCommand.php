<?php

namespace AppBundle\Command;

use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\Profile;
use AppBundle\Entity\Timeline\Theme;
use AppBundle\Timeline\TimelineFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class TimelineImportCommand extends Command
{
    private const BOOLEAN_CHOICES = ['oui' => true, 'non' => false];

    private $em;
    private $factory;

    public function __construct(EntityManagerInterface $em, TimelineFactory $factory)
    {
        $this->em = $em;
        $this->factory = $factory;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:timeline:import')
            ->addArgument('profilesUrl', InputArgument::REQUIRED)
            ->addArgument('themesUrl', InputArgument::REQUIRED)
            ->addArgument('measuresUrl', InputArgument::REQUIRED)
            ->setDescription('Import timeline from CSV files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['', 'Starting Timeline import.']);

        $this->em->beginTransaction();

        $this->importProfiles($input, $output);
        $this->importThemes($input, $output);
        $this->importMeasures($input, $output);

        $this->em->commit();

        $output->writeln(['', 'Timeline imported successfully!']);
    }

    private function importProfiles(InputInterface $input, OutputInterface $output): void
    {
        $profilesUrl = $input->getArgument('profilesUrl');

        $output->writeln(['', sprintf('Starting profiles import from "%s".', $profilesUrl)]);

        $count = 0;
        foreach ($this->parseCSV($profilesUrl) as $index => $row) {
            list($title, $description) = $row;

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

        $output->writeln(sprintf('Saved %s profiles.', $count));
    }

    private function importThemes(InputInterface $input, OutputInterface $output): void
    {
        $themesUrl = $input->getArgument('themesUrl');

        $output->writeln(['', sprintf('Starting themes import from "%s".', $themesUrl)]);

        $count = 0;
        foreach ($this->parseCSV($themesUrl) as $index => $row) {
            list($title, $isFeatured, $description, $imageUrl) = $row;

            if (empty($title)) {
                throw new \RuntimeException(sprintf('No title found for theme. (line %d)', $index + 2));
            }

            $isFeatured = strtolower($isFeatured);
            if (!\array_key_exists($isFeatured, self::BOOLEAN_CHOICES)) {
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

        $output->writeln(sprintf('Saved %d themes.', $count));
    }

    public function importMeasures(InputInterface $input, OutputInterface $output): void
    {
        $savedProfiles = $this->getProfiles();
        $savedThemes = $this->getThemes();

        $measuresUrl = $input->getArgument('measuresUrl');

        $output->writeln(['', sprintf('Starting measures import from "%s".', $measuresUrl)]);

        $count = 0;
        foreach ($this->parseCSV($measuresUrl) as $index => $row) {
            list($title, $status, $isGlobal, $themes, $profiles, $link) = $row;

            if (empty($title)) {
                throw new \RuntimeException(sprintf('No title found for measure. (line %d)', $index + 2));
            }

            if (Measure::TITLE_MAX_LENGTH < mb_strlen($title)) {
                throw new \RuntimeException(sprintf(
                    'Measure title "%s" is too long. (%d characters max).',
                    $title,
                    Measure::TITLE_MAX_LENGTH
                ));
            }

            if (!\array_key_exists($status, Measure::STATUSES)) {
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

                    if (!\array_key_exists($themeTitle, $savedThemes)) {
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

                    if (!\array_key_exists($profileTitle, $savedProfiles)) {
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
                $title,
                Measure::STATUSES[$status],
                $relatedProfiles,
                $relatedThemes,
                $link,
                !empty($isGlobal)
            );

            $this->em->persist($measure);

            ++$count;

            if (0 === ($count % 50)) {
                $this->em->flush();
                $this->em->clear(Measure::class);

                $output->writeln(sprintf('Saved %d measures.', $count));
            }
        }

        $this->em->flush();
        $this->em->clear();

        $output->writeln(sprintf('Saved %d measures.', $count));
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

    private function getProfiles(): array
    {
        /** @var Profile $profile */
        foreach ($this->em->getRepository(Profile::class)->findAll() as $profile) {
            $profiles[$profile->getTitle()] = $profile;
        }

        return $profiles ?? [];
    }

    private function getThemes(): array
    {
        /** @var Theme $theme */
        foreach ($this->em->getRepository(Theme::class)->findAll() as $theme) {
            $themes[$theme->getTitle()] = $theme;
        }

        return $themes ?? [];
    }
}

<?php

namespace AppBundle\Command;

use AppBundle\Command\ChezVous\AbstractImportCommand as ChezVousImportCommand;
use AppBundle\Documents\DocumentRepository;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Media;
use AppBundle\Entity\Summary;
use AppBundle\Entity\TurnkeyProjectFile;
use AppBundle\Entity\UserDocument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StorageMigrateCommand extends Command
{
    private const PRIVATE_PATH = 'private';
    private const PUBLIC_PATH = 'public';

    private const MAPPING = [
        self::PRIVATE_PATH => [
            ChezVousImportCommand::ROOT_DIRECTORY => ChezVousImportCommand::ROOT_DIRECTORY,
            DocumentRepository::DIRECTORY_ROOT => DocumentRepository::DIRECTORY_ROOT,
            'files/application_requests_curriculum' => RunningMateRequest::CURRICULUM_DIRECTORY,
            'images/committees' => Committee::PHOTO_DIRECTORY,
            TimelineImportCommand::CSV_DIRECTORY => TimelineImportCommand::CSV_DIRECTORY,
            'files/turnkey_projects_files' => TurnkeyProjectFile::DIRECTORY,
        ],
        self::PUBLIC_PATH => [
            'images/summaries' => Summary::PROFILE_PICTURE_DIRECTORY,
            'files' => 'files',
            'static' => 'static',
            UserDocument::DIRECTORY => UserDocument::DIRECTORY,
            Media::IMAGES_DIRECTORY => Media::IMAGES_DIRECTORY,
            Media::VIDEOS_DIRECTORY => Media::VIDEOS_DIRECTORY,
        ],
    ];

    protected static $defaultName = 'app:storage:migrate';

    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function configure()
    {
        $this
            ->addArgument('bucket', InputArgument::REQUIRED, 'The bucket name.')
            ->setDescription('Generates the storage migration for private & public adapters.')
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bucket = $input->getArgument('bucket');

        $this->io->title('Starting storage migration');

        foreach (self::MAPPING as $destinationPrefix => $mappings) {
            foreach ($mappings as $source => $destination) {
                $this->io->writeln("gsutil mv -m $bucket/$source $bucket/$destinationPrefix/$destination");
            }
        }

        $this->io->success('Storage migration generated successfully!');
    }
}

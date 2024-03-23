<?php

namespace App\Command;

use App\Entity\Biography\ExecutiveOfficeMember;
use App\Entity\Biography\ExecutiveOfficeRoleEnum;
use App\Image\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Flysystem\FilesystemOperator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsCommand(
    name: 'app:executive-office-members:import',
    description: 'Import Executive Office Members from CSV file',
)]
class ImportExecutiveOfficeMembersCommand extends Command
{
    private const BATCH_SIZE = 250;

    private FilesystemOperator $storage;
    private ImageManager $imageManager;
    private EntityManagerInterface $em;

    /**
     * @var SymfonyStyle|null
     */
    private $io;

    public function __construct(FilesystemOperator $defaultStorage, ImageManager $imageManager, EntityManagerInterface $em)
    {
        $this->storage = $defaultStorage;
        $this->imageManager = $imageManager;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('filename', InputArgument::REQUIRED)
            ->addOption('renaissance', null, InputOption::VALUE_NONE, 'Import for Renaissance.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->em->beginTransaction();

            $this->handleImport(
                $input->getArgument('filename'),
                $input->getOption('renaissance')
            );

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

        return self::SUCCESS;
    }

    private function handleImport(string $filename, bool $forRenaissance): void
    {
        $this->io->section('Starting import of Executive Office Members.');

        $csv = $this->createReader($filename);

        $this->io->progressStart($csv->count());

        $line = 1;
        $count = 0;
        foreach ($csv as $row) {
            ++$line;

            $firstName = trim($row['firstName']);
            $lastName = trim($row['lastName']);
            $position = trim($row['position']);
            $imagePath = trim($row['imagePath']);
            $description = trim($row['description']);
            $content = trim($row['content']);
            $president = !empty(trim($row['president']));
            $executiveOfficer = !empty(trim($row['executiveOfficer']));
            $deputyGeneralDelegate = !empty(trim($row['deputyGeneralDelegate']));
            $facebook = trim($row['facebook']);
            $twitter = trim($row['twitter']);
            $instagram = trim($row['instagram']);
            $linkedIn = trim($row['linkedIn']);

            if (empty($firstName)) {
                $this->io->text("No firstName given, skipping (line $line)");

                continue;
            }

            if (empty($lastName)) {
                $this->io->text("No lastName given, skipping (line $line)");

                continue;
            }

            if (empty($position)) {
                $this->io->text("No position given, skipping (line $line)");

                continue;
            }

            if (empty($imagePath)) {
                $this->io->text("No imagePath given, skipping (line $line)");

                continue;
            }

            if (!$this->storage->has($imagePath)) {
                $this->io->text("Image file \"$imagePath\" not found on storage, skipping (line $line)");

                continue;
            }

            $executiveOfficeMember = new ExecutiveOfficeMember(
                null,
                $firstName,
                $lastName,
                $description,
                $content,
                true,
                $position
            );

            if ($president) {
                $executiveOfficeMember->setRole(ExecutiveOfficeRoleEnum::PRESIDENT);
            } elseif ($executiveOfficer) {
                $executiveOfficeMember->setRole(ExecutiveOfficeRoleEnum::EXECUTIVE_OFFICER);
            } elseif ($deputyGeneralDelegate) {
                $executiveOfficeMember->setRole(ExecutiveOfficeRoleEnum::DEPUTY_GENERAL_DELEGATE);
            }

            if ($forRenaissance) {
                $executiveOfficeMember->setForRenaissance(true);
            }

            if (!empty($facebook)) {
                $executiveOfficeMember->setFacebookProfile($facebook);
            }

            if (!empty($twitter)) {
                $executiveOfficeMember->setTwitterProfile($twitter);
            }

            if (!empty($instagram)) {
                $executiveOfficeMember->setInstagramProfile($instagram);
            }

            if (!empty($linkedIn)) {
                $executiveOfficeMember->setLinkedInProfile($linkedIn);
            }

            $tmpFile = tempnam(sys_get_temp_dir(), uniqid());
            file_put_contents($tmpFile, $this->storage->read($imagePath));

            $executiveOfficeMember->setImage(new UploadedFile(
                $tmpFile,
                Uuid::uuid4().'.'.pathinfo($imagePath, \PATHINFO_EXTENSION),
                $this->storage->mimeType($imagePath),
                null,
                true
            ));

            $this->imageManager->saveImage($executiveOfficeMember);

            $this->em->persist($executiveOfficeMember);
            $this->em->flush();

            ++$count;

            $this->io->progressAdvance();

            if (0 === ($count % self::BATCH_SIZE)) {
                $this->em->clear();
            }
        }

        $this->em->clear();

        $this->io->progressFinish();

        $this->io->success("$count members imported successfully !");
    }

    private function createReader(string $filePath, ?int $headerOffset = 0): Reader
    {
        $csvContent = $this->storage->read($filePath);

        $reader = Reader::createFromString($csvContent);

        if (null !== $headerOffset) {
            $reader->setHeaderOffset($headerOffset);
        }

        return $reader;
    }
}

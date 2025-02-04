<?php

namespace App\Command;

use App\Entity\Event\Event;
use App\Repository\Event\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:events:save-images-informations',
    description: 'Save images informations for all events.',
)]
class SyncEventsImageCommand extends Command
{
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly FilesystemOperator $defaultStorage,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $this->getCount();

        $this->io->text(\sprintf('Found %d event(s) with no image attributes.', $count));

        $this->io->progressStart($count);

        foreach ($this->getEvents() as $event) {
            $imagePath = $event->getImagePath();

            if (!$imagePath || !$this->defaultStorage->has($imagePath)) {
                $this->io->text(\sprintf('Could not find image of event "%s" on storage.', $event->getUuidAsString()));
                $this->io->progressAdvance();
                continue;
            }

            $imageContent = $this->defaultStorage->read($imagePath);

            $imageInfo = getimagesizefromstring($imageContent);

            if (false === $imageInfo) {
                $this->io->text(\sprintf('Could not read image informations of event "%s".', $event->getUuidAsString()));
                $this->io->progressAdvance();
                continue;
            }

            $event->setImageWidth($imageInfo[0]);
            $event->setImageHeight($imageInfo[1]);
            $event->setImageMimeType($imageInfo['mime']);
            $event->setImageSize(\strlen($imageContent));

            $this->entityManager->flush();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->success('Finished saving images informations');

        return self::SUCCESS;
    }

    private function getCount(): int
    {
        return $this
            ->getQueryBuilder()
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /** @return Event[] */
    private function getEvents(): array
    {
        return $this
            ->getQueryBuilder()
            ->getQuery()
            ->getResult()
        ;
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this
            ->eventRepository
            ->createQueryBuilder('e')
            ->where('e.imageName IS NOT NULL')
            ->andWhere('e.imageSize IS NULL')
        ;
    }
}

<?php

namespace App\Command;

use App\Entity\Event\Event;
use App\Repository\Event\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Exception\CommonMarkException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tiptap\Editor;

#[AsCommand(
    name: 'app:event:transform-description',
    description: 'Transform event description to HTML.',
)]
class TransformEventDescriptionCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventRepository $eventRepository,
        private readonly CommonMarkConverter $markConverter,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $events = $this->getEventToTransform();
        $tipTapEditor = new Editor();

        $this->io->progressStart(\count($events));

        foreach ($events as $event) {
            try {
                $html = $this->markConverter->convert($event->getDescription());
            } catch (CommonMarkException $e) {
                $this->io->error(\sprintf('Error for event %s: %s', $event->getId(), $e->getMessage()));
                continue;
            }

            $event->jsonDescription = $tipTapEditor->setContent($html->getContent())->getJSON();

            $this->entityManager->flush();
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        return Command::SUCCESS;
    }

    /**
     * @return Event[]
     */
    private function getEventToTransform(): array
    {
        return $this->eventRepository
            ->createQueryBuilder('e')
            ->where('e.jsonDescription IS NULL')
            ->andWhere('YEAR(e.beginAt) >= 2024')
            ->getQuery()
            ->getResult()
        ;
    }
}

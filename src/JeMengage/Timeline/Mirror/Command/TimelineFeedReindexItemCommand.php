<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror\Command;

use App\JeMengage\Timeline\Mirror\Message\UpsertTimelineFeedCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:timeline:reindex-item',
    description: 'Reindex a single timeline entity into the mirror, through the same async path as live indexing.',
)]
class TimelineFeedReindexItemCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('class', InputArgument::REQUIRED, 'Fully-qualified entity class name')
            ->addArgument('id', InputArgument::REQUIRED, 'Entity identifier')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $class */
        $class = $input->getArgument('class');
        /** @var string $id */
        $id = $input->getArgument('id');

        if (!class_exists($class) || $this->entityManager->getMetadataFactory()->isTransient($class)) {
            $io->error(\sprintf('"%s" is not a mapped Doctrine entity.', $class));

            return Command::INVALID;
        }

        $this->bus->dispatch(new UpsertTimelineFeedCommand($class, $id));

        $io->success(\sprintf('Reindex message dispatched for %s#%s.', $class, $id));

        return Command::SUCCESS;
    }
}

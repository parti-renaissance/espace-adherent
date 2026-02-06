<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Procuration\RequestSlot;
use App\Entity\Procuration\Round;
use App\Procuration\Command\MatchedRequestSlotReminderCommand;
use App\Repository\Procuration\RequestSlotRepository;
use App\Repository\Procuration\RoundRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:procuration:remind-matched-slot',
    description: 'This command finds matched slots and sends an email reminder',
)]
class RemindProcurationMatchedSlotCommand extends Command
{
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly RoundRepository $roundRepository,
        private readonly RequestSlotRepository $requestSlotRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('round', null, InputOption::VALUE_REQUIRED, 'Upcoming Round ID to remind.')
            ->addOption('matched-before', null, InputOption::VALUE_REQUIRED, 'Datetime before matching was made.')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, '', 500)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Procuration matched slot reminder');

        $this->io->section('Fetch upcoming round');

        $roundId = $input->getOption('round');

        if ($roundId && !\is_int($roundId)) {
            $this->io->error(\sprintf('Option "round" must be an integer, "%s" given.', $roundId));

            return self::INVALID;
        }

        $round = $this->findUpcomingRound($input->getOption('round'));

        if (!$round) {
            $this->io->error('Could not find any upcoming round.');

            return self::FAILURE;
        }

        $this->io->table(
            ['Élections', 'Tour', 'Date'],
            [
                [
                    $round->election->name,
                    $round->name,
                    $round->date?->format('Y-m-d'),
                ],
            ]
        );

        $this->io->section('Fetch procurations to remind');

        try {
            $matchedBefore = new \DateTime($input->getOption('matched-before') ?? 'now');
        } catch (\Exception $e) {
            $this->io->error('Provided option "matched-before" is an invalid datetime format.');

            return self::INVALID;
        }

        $paginator = $this->getRequestSlotsToRemindQueryBuilder($round, $matchedBefore);
        $total = $paginator->count();

        if (0 === $total) {
            $this->io->info('Found no request slot to remind.');

            return self::SUCCESS;
        }

        $batchSize = $input->getOption('batch-size');

        $this->io->text(\sprintf('Found %s matched request slot(s) to remind for given round.', $total));

        if (!$this->io->confirm(\sprintf('Send reminder to %s matched request slot(s)?', $total), true)) {
            return self::FAILURE;
        }

        $paginator->getQuery()->setMaxResults($batchSize);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $requestSlot) {
                $this->io->text($requestSlot->getUuid()->toString());
                $this->bus->dispatch(new MatchedRequestSlotReminderCommand($requestSlot->getUuid()));

                $this->io->progressAdvance();
                ++$offset;
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->entityManager->clear();
        } while ($offset < $total);

        $this->io->progressFinish();

        $this->io->success('Done.');

        return self::SUCCESS;
    }

    private function findUpcomingRound(?int $roundId = null): ?Round
    {
        $qb = $this->roundRepository
            ->createQueryBuilder('round')
            ->andWhere('round.date > :now')
            ->setParameter('now', new \DateTime())
        ;

        if ($roundId) {
            $qb
                ->andWhere('round.id = :round_id')
                ->setParameter('round_id', $roundId)
            ;
        }

        return $qb
            ->orderBy('round.date', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Paginator|RequestSlot[]
     */
    private function getRequestSlotsToRemindQueryBuilder(Round $round, \DateTime $matchedBefore): Paginator
    {
        return new Paginator(
            $this
                ->requestSlotRepository
                ->findAllMatchedToRemindQueryBuilder($round, $matchedBefore)
                ->getQuery()
        );
    }
}

<?php

namespace App\Command;

use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Mailer\MailerService;
use App\Procuration\ProcurationProxyMessageFactory;
use App\Repository\ProcurationProxyRepository;
use App\Repository\ProcurationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'app:procuration:send-reminder',
    description: 'Send a reminder to the procuration proxies.',
)]
class ProcurationSendReminderCommand extends Command
{
    private EntityManagerInterface $manager;
    private ProcurationProxyMessageFactory $factory;
    private MailerService $mailer;
    private SymfonyStyle $io;

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm without sending any email and without persisting any data.')
            ->addArgument('procuration-mode', InputArgument::REQUIRED, 'Mode : procuration request : 1 or procuration proxy : 2')
            ->addArgument('processed-after', InputArgument::REQUIRED, 'Date - Processed after')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mode = (int) $input->getArgument('procuration-mode');
        $processedAfter = new \DateTime($input->getArgument('processed-after'));

        $paginator = $this->getQueryBuilder($mode, $processedAfter, 200);

        if (!$count = $paginator->count()) {
            $output->writeln('No reminder to send');

            return self::SUCCESS;
        }

        if ($input->getOption('dry-run')) {
            $this->io->note($count.' reminders would be sent');

            return self::SUCCESS;
        }

        if (false === $this->io->confirm(sprintf('Are you sure to send the reminder to %d %s?', $count, 1 === $mode ? 'requests' : 'proxies'), false)) {
            return self::SUCCESS;
        }

        $this->io->progressStart($count);
        $offset = 0;

        do {
            /** @var ProcurationRequest[]|ProcurationProxy[] $objects */
            $objects = iterator_to_array($paginator);

            try {
                if (1 === $mode) {
                    $message = $this->factory->createRequestReminderMessage($objects);
                } else {
                    $message = $this->factory->createProxyReminderMessage($objects);
                }

                $this->mailer->sendMessage($message);

                foreach ($objects as $request) {
                    $request->remind();
                }

                $this->manager->flush();
            } catch (\Throwable $e) {
                $this->io->error($e->getMessage());
            }

            $this->io->progressAdvance($currentCount = \count($objects));
            $offset += $currentCount;

            $paginator->getQuery()->setFirstResult($offset);

            $this->manager->clear();
        } while ($offset < $count);

        $this->io->progressFinish();

        $this->io->note($offset.' reminders sent');

        return self::SUCCESS;
    }

    #[Required]
    public function setManager(EntityManagerInterface $manager): void
    {
        $this->manager = $manager;
    }

    #[Required]
    public function setFactory(ProcurationProxyMessageFactory $factory): void
    {
        $this->factory = $factory;
    }

    #[Required]
    public function setMailer(MailerService $transactionalMailer): void
    {
        $this->mailer = $transactionalMailer;
    }

    private function getQueryBuilder(int $mode, \DateTime $processedAfter, int $limit): Paginator
    {
        if (1 === $mode) {
            /** @var ProcurationRequestRepository $repository */
            $repository = $this->manager->getRepository(ProcurationRequest::class);
        } else {
            /** @var ProcurationProxyRepository $repository */
            $repository = $this->manager->getRepository(ProcurationProxy::class);
        }

        return new Paginator($repository->createQueryBuilderForReminders($processedAfter, $limit)->getQuery());
    }
}

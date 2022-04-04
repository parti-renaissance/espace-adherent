<?php

namespace App\Command;

use App\Entity\ProcurationRequest;
use App\Procuration\ProcurationReminderHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcurationSendReminderCommand extends Command
{
    protected static $defaultName = 'app:procuration:send-reminder';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $manager;

    /**
     * @var \App\Procuration\ProcurationReminderHandler
     */
    private $reminder;

    private $io;

    protected function configure()
    {
        $this
            ->setDescription('Send a reminder to the procuration proxies.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm without sending any email and without persisting any data.')
            ->addArgument('processed-after', InputArgument::REQUIRED, 'Date - Processed after')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $procurationRequestRepository = $this->manager->getRepository(ProcurationRequest::class);

        $processedAfter = new \DateTime($input->getArgument('processed-after'));

        if (!$totalCount = $procurationRequestRepository->countRemindersToSend($processedAfter)) {
            $output->writeln('No reminder to send');

            return 0;
        }

        if ($input->getOption('dry-run')) {
            $output->writeln($totalCount.' reminders would be sent');

            return 0;
        }

        $progress = new ProgressBar($output, $totalCount);
        $progress->setFormat('debug');

        for ($i = 0;; ++$i) {
            $requests = $procurationRequestRepository->findRemindersBatchToSend($processedAfter, 1);

            if (empty($requests)) {
                break;
            }

            try {
                $this->reminder->remind($requests);
            } catch (\Throwable $e) {
                $this->io->error($e->getMessage());
            }

            $progress->advance();

            $this->manager->flush();
            $this->manager->clear();
        }

        $progress->finish();
        $output->writeln("\n".$totalCount.' reminders sent');

        return 0;
    }

    /** @required */
    public function setManager(EntityManagerInterface $manager): void
    {
        $this->manager = $manager;
    }

    /** @required */
    public function setReminder(ProcurationReminderHandler $reminder): void
    {
        $this->reminder = $reminder;
    }
}

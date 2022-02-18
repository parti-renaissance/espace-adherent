<?php

namespace App\Command\Procuration;

use App\Entity\ProcurationRequest;
use App\Procuration\ProcurationReminderHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SendRequestAdministrativeReminderCommand extends Command
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $manager;

    /**
     * @var \App\Procuration\ProcurationReminderHandler
     */
    private $reminder;

    public const COMMAND_NAME = 'app:procuration:send-request-administrative-reminder';
    public const PER_PAGE = 200;

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Send a reminder to the procuration requests.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm without sending any email and without persisting any data.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $procurationRequestRepository = $this->manager->getRepository(ProcurationRequest::class);

        $totalCount = ceil($procurationRequestRepository->countVoteRemindersToSend() / self::PER_PAGE);
        if (!$totalCount) {
            $output->writeln('No reminder to send');

            return;
        }

        if ($input->getOption('dry-run')) {
            $output->writeln($totalCount.' reminders would be sent');

            return;
        }

        $progress = new ProgressBar($output, $totalCount);
        $progress->setFormat('debug');

        for ($i = 0;; ++$i) {
            $requests = $procurationRequestRepository->findRemindersBatchToSend($i * self::PER_PAGE, self::PER_PAGE);

            if (empty($requests)) {
                break;
            }

            $progress->advance();

            try {
                $this->reminder->remind($requests);
            } catch (\Throwable $e) {
            }

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

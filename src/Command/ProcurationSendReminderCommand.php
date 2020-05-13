<?php

namespace App\Command;

use App\Entity\ProcurationRequest;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcurationSendReminderCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $manager;

    /**
     * @var \App\Procuration\ProcurationReminderHandler
     */
    private $reminder;

    public const COMMAND_NAME = 'app:procuration:send-reminder';
    public const PER_PAGE = 200;

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Send a reminder to the procuration proxies.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm without sending any email and without persisting any data.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->manager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->reminder = $this->getContainer()->get('app.procuration.reminder_handler');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $procurationRequestRepository = $this->manager->getRepository(ProcurationRequest::class);

        $totalCount = ceil($procurationRequestRepository->countRemindersToSend() / self::PER_PAGE);
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
    }
}

<?php

namespace AppBundle\Command;

use AppBundle\Entity\ProcurationRequest;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SendMailToProcurationProxiesCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $manager;

    /**
     * @var \AppBundle\Procuration\ProcurationReminderHandler
     */
    private $reminder;

    /**
     * @var int
     */
    private $limit;

    public const COMMAND_NAME = 'app:mail:send-reminder-to-procuration-proxy';
    private const MAILS_PER_LOOP = 'mails-per-loop';
    private const MAILS_PER_LOOP_DEFAULT_VALUE = 25;

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Send a reminder, 48h after the match, to the procuration proxies.')
            ->addOption(self::MAILS_PER_LOOP, 'm', InputOption::VALUE_OPTIONAL, 'Adapt the number of emails to manage per loop if you have a memory limit issue.', self::MAILS_PER_LOOP_DEFAULT_VALUE)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->manager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->reminder = $this->getContainer()->get('app.procuration.reminder_handler');

        $this->limit = is_numeric($input->getOption(self::MAILS_PER_LOOP))
            ? intval($input->getOption(self::MAILS_PER_LOOP))
            : self::MAILS_PER_LOOP_DEFAULT_VALUE;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $procurationRequestRepository = $this->manager->getRepository(ProcurationRequest::class);
        for ($i = 0, $total = 0; ; ++$i) {
            $requests = $procurationRequestRepository->paginateRequestForSendReminderToProxies($i, $this->limit);

            foreach ($requests as $request) {
                $this->reminder->remind($request);
            }
            $this->manager->flush();
            $this->manager->clear();

            $total += $count = count($requests);
            if ($this->limit !== $count) {
                break;
            }
        }

        $output->writeln(sprintf('<comment>%d</comment> reminders sent.', $total));
    }
}

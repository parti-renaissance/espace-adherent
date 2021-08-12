<?php

namespace App\Command;

use App\Entity\Jecoute\DataSurvey;
use App\Mailchimp\Synchronisation\Command\DataSurveyCreateCommand;
use App\Repository\Jecoute\DataSurveyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncJecouteCommand extends Command
{
    protected static $defaultName = 'mailchimp:sync:jecoute';

    private $dataSurveyRepository;
    private $entityManager;
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        DataSurveyRepository $dataSurveyRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus
    ) {
        $this->dataSurveyRepository = $dataSurveyRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('limit');

        $paginator = $this->getQueryBuilder();

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(sprintf('Are you sure to sync %d emails from "J\'écoute" ?', $total), false)) {
            return 1;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 2 ? $limit : 2);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            /** @var DataSurvey $dataSurvey */
            foreach ($paginator->getIterator() as $dataSurvey) {
                $this->io->comment($dataSurvey->getEmailAddress());

                $this->bus->dispatch(new DataSurveyCreateCommand($dataSurvey->getEmailAddress()));

                $this->io->progressAdvance();
                ++$offset;
                if ($limit && $limit <= $offset) {
                    break 2;
                }
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->entityManager->clear();
        } while ($offset < $count && (!$limit || $offset < $limit));

        $this->io->progressFinish();

        return 0;
    }

    private function getQueryBuilder(): Paginator
    {
        $queryBuilder = $this
            ->dataSurveyRepository
            ->createAvailableToContactQueryBuilder()
        ;

        return new Paginator($queryBuilder->getQuery());
    }
}

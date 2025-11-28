<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Mailchimp\Synchronisation\Command\JemarcheDataSurveyCreateCommand;
use App\Repository\Jecoute\JemarcheDataSurveyRepository;
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
    name: 'mailchimp:sync:jecoute',
)]
class MailchimpSyncJecouteCommand extends Command
{
    private $dataSurveyRepository;
    private $entityManager;
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        JemarcheDataSurveyRepository $dataSurveyRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
    ) {
        $this->dataSurveyRepository = $dataSurveyRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');

        $paginator = $this->getQueryBuilder();

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(\sprintf('Are you sure to sync %d emails from "J\'écoute" ?', $total), false)) {
            return self::FAILURE;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 2 ? $limit : 2);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            /** @var JemarcheDataSurvey $dataSurvey */
            foreach ($paginator->getIterator() as $dataSurvey) {
                $this->io->comment($dataSurvey->getEmailAddress());

                $this->bus->dispatch(new JemarcheDataSurveyCreateCommand($dataSurvey->getEmailAddress()));

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

        return self::SUCCESS;
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

<?php

namespace App\Command;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeChangeCommand;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncAllElectedRepresentativesCommand extends Command
{
    protected static $defaultName = 'mailchimp:sync:all-elected-representatives';

    private $electedRepresentativeRepository;
    private $entityManager;
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        ElectedRepresentativeRepository $electedRepresentativeRepository,
        ObjectManager $entityManager,
        MessageBusInterface $bus
    ) {
        $this->electedRepresentativeRepository = $electedRepresentativeRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, null)
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

        if (false === $this->io->confirm(sprintf('Are you sure to sync %d adherents?', $total), false)) {
            return 1;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            /** @var ElectedRepresentative $electedRepresentative */
            foreach ($paginator->getIterator() as $electedRepresentative) {
                $this->bus->dispatch(new ElectedRepresentativeChangeCommand(
                    $electedRepresentative->getUuid(),
                    $electedRepresentative->getEmailAddress()
                ));
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
    }

    private function getQueryBuilder(): Paginator
    {
        $queryBuilder = $this
            ->electedRepresentativeRepository
            ->createWithEmailQueryBuilder()
        ;

        return new Paginator($queryBuilder->getQuery());
    }
}

<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use App\SendInBlue\Command\AdherentSynchronisationCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class SendInBlueSyncAllContactsCommand extends Command
{
    protected static $defaultName = 'sendinblue:sync:all-contacts';

    private AdherentRepository $adherentRepository;
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $bus;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        AdherentRepository $adherentRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
            ->setDescription('Dispatch adherents synchronisation.')
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('limit');

        $paginator = $this->getPaginator();

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (0 === $total) {
            $this->io->note('No adherent to process.');

            return 0;
        }

        if (false === $this->io->confirm(sprintf('Are you sure to dispatch synchronisation of %d adherents?', $total), false)) {
            return 1;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $adherent) {
                $this->dispatchSynchronisation($adherent);

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

    /**
     * @return Paginator|Adherent[]
     */
    private function getPaginator(): Paginator
    {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
        ;

        return new Paginator($queryBuilder->getQuery());
    }

    private function dispatchSynchronisation(Adherent $adherent): void
    {
        $this->bus->dispatch(new AdherentSynchronisationCommand($adherent->getUuid(), $adherent->getEmailAddress()));
    }
}

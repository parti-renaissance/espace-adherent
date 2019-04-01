<?php

namespace AppBundle\Command;

use AppBundle\AdherentMessage\Command\CreateCommitteeStaticSegmentCommand;
use AppBundle\Entity\Committee;
use AppBundle\Repository\CommitteeRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncAllCommitteesCommand extends Command
{
    protected static $defaultName = 'mailchimp:sync:all-committees';

    private $repository;
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(CommitteeRepository $repository, MessageBusInterface $bus)
    {
        $this->repository = $repository;
        $this->bus = $bus;

        parent::__construct();
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->progressStart();

        foreach ($this->getCommittees() as $committee) {
            $this->bus->dispatch(new CreateCommitteeStaticSegmentCommand(current($committee)->getUuid()));
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
    }

    /**
     * @return Committee[]
     */
    private function getCommittees(): iterable
    {
        return $this->repository
            ->createQueryBuilder('committee')
            ->where('committee.status = :status')
            ->andWhere('committee.mailchimpId IS NULL')
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
            ->iterate()
        ;
    }
}

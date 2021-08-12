<?php

namespace App\Command;

use App\Newsletter\Command\MailchimpSyncNewsletterSubscriptionEntityCommand;
use App\Repository\NewsletterSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncAllNewsletterCommand extends Command
{
    protected static $defaultName = 'mailchimp:sync:all-newsletter';

    private $repository;
    private $entityManager;
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        NewsletterSubscriptionRepository $repository,
        ObjectManager $entityManager,
        MessageBusInterface $bus
    ) {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Send all newsletter subscription to Mailchimp');
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $paginator = $this->getPaginator();

        $count = $paginator->count();

        if (false === $this->io->confirm(sprintf('Are you sure to sync %d newsletters?', $count), false)) {
            return 1;
        }

        $this->io->progressStart($count);
        $offset = 0;

        do {
            foreach ($paginator->getIterator() as $newsletter) {
                $this->bus->dispatch(new MailchimpSyncNewsletterSubscriptionEntityCommand($newsletter->getId()));

                $this->io->progressAdvance();

                ++$offset;
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->entityManager->clear();
        } while (0 !== $offset && $offset < $count);

        $this->io->progressFinish();

        return 0;
    }

    private function getPaginator(): Paginator
    {
        $queryBuilder = $this->repository
            ->disableSoftDeleteableFilter()
            ->createQueryBuilder('newsletter')
            ->setMaxResults(500)
        ;

        return new Paginator($queryBuilder->getQuery());
    }
}

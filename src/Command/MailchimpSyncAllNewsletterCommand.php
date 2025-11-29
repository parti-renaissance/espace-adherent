<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\NewsletterSubscription as EMNewsletterSubscription;
use App\Entity\Renaissance\NewsletterSubscription as RenaissanceNewsletterSubscription;
use App\Newsletter\Command\MailchimpSyncNewsletterSubscriptionEntityCommand;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'mailchimp:sync:all-newsletter',
    description: 'Send all newsletter subscription to Mailchimp',
)]
class MailchimpSyncAllNewsletterCommand extends Command
{
    private const TYPE_EM = 'en-marche';
    private const TYPE_RENAISSANCE = 'renaissance';

    private $entityManager;
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(ObjectManager $entityManager, MessageBusInterface $bus)
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, 'Type of newsletters to sync: '.implode(' or ', [self::TYPE_EM, self::TYPE_RENAISSANCE]))
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        if (self::TYPE_RENAISSANCE !== $type) {
            $type = self::TYPE_EM;
        }

        $paginator = $this->getPaginator($type);

        $count = $paginator->count();

        if (false === $this->io->confirm(\sprintf('Are you sure to sync %d newsletters?', $count), false)) {
            return self::FAILURE;
        }

        $this->io->progressStart($count);
        $offset = 0;

        do {
            foreach ($paginator->getIterator() as $newsletter) {
                $this->bus->dispatch(new MailchimpSyncNewsletterSubscriptionEntityCommand($newsletter::class, $newsletter->getId()));

                $this->io->progressAdvance();

                ++$offset;
            }

            $paginator->getQuery()->setFirstResult($offset);

            $this->entityManager->clear();
        } while (0 !== $offset && $offset < $count);

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    private function getPaginator(string $type): Paginator
    {
        $queryBuilder = $this->entityManager->getRepository(
            self::TYPE_RENAISSANCE === $type ?
                RenaissanceNewsletterSubscription::class :
                EMNewsletterSubscription::class
        )
            ->createQueryBuilderForSynchronization()
            ->setMaxResults(500)
        ;

        return new Paginator($queryBuilder->getQuery());
    }
}

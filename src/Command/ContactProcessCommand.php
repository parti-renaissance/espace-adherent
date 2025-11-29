<?php

declare(strict_types=1);

namespace App\Command;

use App\Contact\ContactHandler;
use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:contact:process',
    description: 'Dispatch contacts process.',
)]
class ContactProcessCommand extends Command
{
    private ContactRepository $contactRepository;
    private ContactHandler $contactHandler;
    private EntityManagerInterface $entityManager;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        ContactRepository $contactRepository,
        ContactHandler $contactHandler,
        EntityManagerInterface $entityManager,
    ) {
        $this->contactRepository = $contactRepository;
        $this->contactHandler = $contactHandler;
        $this->entityManager = $entityManager;

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');

        $paginator = $this->getPaginator();

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (0 === $total) {
            $this->io->note('No contact to process.');

            return self::SUCCESS;
        }

        if (false === $this->io->confirm(\sprintf('Are you sure to dispatch process of %d contacts?', $total), false)) {
            return self::FAILURE;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $contact) {
                $this->contactHandler->dispatchProcess($contact);

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

    /**
     * @return Paginator|Contact[]
     */
    private function getPaginator(): Paginator
    {
        $queryBuilder = $this->contactRepository
            ->createQueryBuilder('contact')
            ->andWhere('contact.birthdate IS NOT NULL')
            ->andWhere('contact.processedAt IS NULL')
        ;

        return new Paginator($queryBuilder->getQuery());
    }
}

<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncAllAdherentsCommand extends Command
{
    protected static $defaultName = 'mailchimp:sync:all-adherents';

    private $adherentRepository;
    private $entityManager;
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        AdherentRepository $adherentRepository,
        ObjectManager $entityManager,
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
            ->addOption('ref-tags', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('disabled-only', null, InputOption::VALUE_NONE)
            ->addOption('certified-only', null, InputOption::VALUE_NONE)
            ->addOption('committee-voter-only', null, InputOption::VALUE_NONE)
            ->addOption('source', null, InputOption::VALUE_REQUIRED)
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('limit');

        $paginator = $this->getQueryBuilder(
            $input->getOption('ref-tags'),
            $input->getOption('disabled-only'),
            $input->getOption('certified-only'),
            $input->getOption('committee-voter-only'),
            $input->getOption('source')
        );

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(sprintf('Are you sure to sync %d adherents?', $total), false)) {
            return 1;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $adherent) {
                $this->bus->dispatch(new AdherentChangeCommand(
                    $adherent->getUuid(),
                    $adherent->getEmailAddress()
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

        return 0;
    }

    /**
     * @return Paginator|Adherent[]
     */
    private function getQueryBuilder(
        array $refTags,
        bool $disabledOnly,
        bool $certifiedOnly,
        bool $committeeVoterOnly,
        ?string $source
    ): Paginator {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->where('adherent.status = :status')
            ->setParameter('status', $disabledOnly ? Adherent::DISABLED : Adherent::ENABLED)
        ;

        if ($source) {
            $queryBuilder
                ->andWhere('adherent.source = :source')
                ->setParameter('source', $source)
            ;
        } else {
            $queryBuilder
                ->andWhere('adherent.adherent = true')
                ->andWhere('adherent.source IS NULL')
            ;
        }

        if ($refTags) {
            $queryBuilder
                ->innerJoin('adherent.referentTags', 'tag')
                ->andWhere('tag.code IN(:tags)')
                ->setParameter('tags', $refTags)
            ;
        }

        if ($certifiedOnly) {
            $queryBuilder->andWhere('adherent.certifiedAt IS NOT NULL');
        }

        if ($committeeVoterOnly) {
            $queryBuilder
                ->innerJoin(
                    'adherent.memberships',
                    'membership',
                    Join::WITH,
                    'membership.adherent = adherent AND membership.enableVote IS NOT NULL'
                )
            ;
        }

        return new Paginator($queryBuilder->getQuery());
    }
}

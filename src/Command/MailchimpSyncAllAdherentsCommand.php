<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\Synchronisation\Command\AdherentChangeChangeCommand;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
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

    public function __construct(AdherentRepository $adherentRepository, ObjectManager $entityManager, MessageBusInterface $bus)
    {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, null)
            ->addOption('ref-tags', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('limit');
        if ($limit < 1) {
            throw new InvalidOptionException(sprintf('Limit value is invalid (%d)', $limit));
        }

        $paginator = $this->getQueryBuilder($input->getOption('ref-tags'));

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(sprintf('Are you sure to sync %d adherents?', $total), false)) {
            return 1;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator->getIterator() as $adherent) {
                $this->bus->dispatch(new AdherentChangeChangeCommand(
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
    }

    private function getQueryBuilder(array $refTags): Paginator
    {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->where('adherent.status = :status')
            ->setParameter('status', Adherent::ENABLED)
        ;

        if ($refTags) {
            $queryBuilder
                ->innerJoin('adherent.referentTags', 'tag')
                ->andWhere('tag.code IN(:tags)')
                ->setParameter('tags', $refTags)
            ;
        }

        return new Paginator($queryBuilder->getQuery());
    }
}

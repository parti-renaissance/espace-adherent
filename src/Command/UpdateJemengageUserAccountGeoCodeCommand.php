<?php

namespace App\Command;

use App\Membership\AdherentEvents;
use App\Membership\Event\AdherentEvent;
use App\Membership\MembershipSourceEnum;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UpdateJemengageUserAccountGeoCodeCommand extends Command
{
    protected static $defaultName = 'jme-adherent:update:geocode';

    private AdherentRepository $adherentRepository;
    private ObjectManager $entityManager;
    private EventDispatcherInterface $dispatcher;
    private SymfonyStyle $io;

    public function __construct(
        AdherentRepository $adherentRepository,
        ObjectManager $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');

        $paginator = $this->getQueryBuilder();
        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(sprintf('Are you sure to update %d JME account(s)?', $total), false)) {
            return 1;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $adherent) {
                try {
                    $this->dispatcher->dispatch(new AdherentEvent($adherent), AdherentEvents::REGISTRATION_COMPLETED);
                } catch (\Exception $e) {
                    $this->io->comment(sprintf(
                        'Error while updating JME account "%s". Message: "%s".',
                        $adherent->getId(),
                        $e->getMessage()
                    ));
                }

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
        $this->io->note($offset.' account(s) updated');

        return 0;
    }

    private function getQueryBuilder(): Paginator
    {
        $queryBuilder = $this->adherentRepository
            ->createQueryBuilder('adherent')
            ->where('adherent.source = :source')
            ->andWhere('adherent.postAddress.latitude IS NULL AND adherent.postAddress.longitude IS NULL')
            ->setParameter('source', MembershipSourceEnum::JEMENGAGE)
        ;

        return new Paginator($queryBuilder->getQuery());
    }
}

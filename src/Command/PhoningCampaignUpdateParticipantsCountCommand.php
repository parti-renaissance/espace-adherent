<?php

namespace App\Command;

use App\Entity\Phoning\Campaign;
use App\Repository\AdherentRepository;
use App\Repository\Phoning\CampaignRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PhoningCampaignUpdateParticipantsCountCommand extends Command
{
    protected static $defaultName = 'phoning-campaign:update:participants-count';

    private AdherentRepository $adherentRepository;
    private CampaignRepository $campaignRepository;
    private ObjectManager $entityManager;
    private SymfonyStyle $io;

    public function __construct(
        AdherentRepository $adherentRepository,
        CampaignRepository $campaignRepository,
        ObjectManager $entityManager
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
        $this->campaignRepository = $campaignRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL)
            ->addOption('has-no-participants-count', InputOption::VALUE_NONE)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('limit');

        $paginator = $this->getQueryBuilder($input->getOption('has-no-participants-count'));

        $count = $paginator->count();
        $total = $limit && $limit < $count ? $limit : $count;

        if (false === $this->io->confirm(sprintf('Are you sure to update %d phoning campaign?', $total), false)) {
            return 1;
        }

        $paginator->getQuery()->setMaxResults($limit && $limit < 500 ? $limit : 500);

        $this->io->progressStart($total);
        $offset = 0;

        do {
            foreach ($paginator as $campaign) {
                try {
                    $campaign->setParticipantsCount($this->adherentRepository->findForPhoningCampaign($campaign)->getTotalItems());

                    $this->entityManager->flush();
                } catch (\Exception $e) {
                    $this->io->comment(sprintf(
                        'Error while updating campaign "%s". Message: "%s".',
                        $campaign->getId(),
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

        return 0;
    }

    /**
     * @return Paginator|Campaign[]
     */
    private function getQueryBuilder(bool $hasNoParticipantsCount): Paginator
    {
        $queryBuilder = $this->campaignRepository
            ->createQueryBuilder('campaign')
            ->innerJoin('campaign.audience', 'audience')
        ;

        if ($hasNoParticipantsCount) {
            $queryBuilder
                ->andWhere('campaign.participantsCount = :nb_participant')
                ->setParameter('nb_participant', 0)
            ;
        }

        return new Paginator($queryBuilder->getQuery());
    }
}

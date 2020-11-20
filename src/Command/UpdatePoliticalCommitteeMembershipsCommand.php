<?php

namespace App\Command;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Repository\TerritorialCouncil\PoliticalCommitteeMembershipRepository;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdatePoliticalCommitteeMembershipsCommand extends Command
{
    private const BATCH_SIZE = 1000;

    protected static $defaultName = 'app:territorial-council:update-political-committee-membership';

    private $em;
    private $politicalCommitteeManager;
    private $pcMembershipRepository;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        EntityManagerInterface $em,
        PoliticalCommitteeManager $politicalCommitteeManager,
        PoliticalCommitteeMembershipRepository $pcMembershipRepository
    ) {
        $this->em = $em;
        $this->politicalCommitteeManager = $politicalCommitteeManager;
        $this->pcMembershipRepository = $pcMembershipRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Update political committee memberships (only qualities that cannot be elected).')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Starting updating political committee memberships.');

        $this->io->progressStart($this->getTerritorialCouncilMembershipCount());

        $count = 0;
        foreach ($this->getTerritorialCouncilMembership() as $result) {
            /* @var TerritorialCouncilMembership $tcMembership */
            $tcMembership = $result[0];

            $pcMembership = $this->pcMembershipRepository->findOneBy([
                'politicalCommittee' => $tcMembership->getTerritorialCouncil()->getPoliticalCommittee(),
                'adherent' => $tcMembership->getAdherent(),
            ]);

            if ($pcMembership) {
                $this->politicalCommitteeManager->updateOfficioMembersFromTerritorialCouncilMembership($pcMembership, $tcMembership);
            } else {
                $this->politicalCommitteeManager->createMembershipFromTerritorialCouncilMembership($tcMembership);
            }

            $this->em->clear();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success('Political committee memberships have been updated successfully!');
    }

    private function getTerritorialCouncilMembership(): IterableResult
    {
        return $this
            ->createTerritorialCouncilMembershipQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function getTerritorialCouncilMembershipCount(): int
    {
        return $this
            ->createTerritorialCouncilMembershipQueryBuilder()
            ->select('COUNT(membership)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createTerritorialCouncilMembershipQueryBuilder(): QueryBuilder
    {
        return $this
            ->em
            ->getRepository(TerritorialCouncilMembership::class)
            ->createQueryBuilder('membership')
            ->select('membership', 'adherent', 'territorialCouncil')
            ->join('membership.adherent', 'adherent')
            ->join('membership.territorialCouncil', 'territorialCouncil')
        ;
    }
}

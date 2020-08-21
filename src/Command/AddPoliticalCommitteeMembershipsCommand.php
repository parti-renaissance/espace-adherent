<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Oneshot command, can be deleted after execution.
 */
class AddPoliticalCommitteeMembershipsCommand extends Command
{
    private const BATCH_SIZE = 1000;

    protected static $defaultName = 'app:territorial-council:add-political-committee-membership';

    private $em;
    private $politicalCommitteeManager;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(EntityManagerInterface $em, PoliticalCommitteeManager $politicalCommitteeManager)
    {
        $this->em = $em;
        $this->politicalCommitteeManager = $politicalCommitteeManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add political committee memberships to adherents.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Starting adding political committee memberships to adherents.');

        $this->io->progressStart($this->getTerritorialCouncilMembershipCount());

        $count = 0;
        /** @var Adherent $adherent */
        foreach ($this->getTerritorialCouncilMembership() as $result) {
            /* @var TerritorialCouncilMembership $tcMembership */
            $tcMembership = $result[0];
            $this->politicalCommitteeManager->createMembershipFromTerritorialCouncilMembership($tcMembership);

            if (0 === (++$count % self::BATCH_SIZE)) {
                $this->em->clear();
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success('Political committee memberships have been added successfully to adherents!');
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

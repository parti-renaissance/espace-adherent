<?php

namespace App\Command;

use App\Entity\Coalition\CauseFollower;
use App\Repository\Coalition\CauseFollowerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Oneshot command, can be deleted after execution.
 */
class CreateCoalitionFollowerFromCauseFollowerCommand extends Command
{
    private const BATCH_SIZE = 1000;

    protected static $defaultName = 'app:coalitions:create-follower';

    private $em;
    private $causeFollowerRepository;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(EntityManagerInterface $em, CauseFollowerRepository $causeFollowerRepository)
    {
        $this->em = $em;
        $this->causeFollowerRepository = $causeFollowerRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create a coalition follower from a cause follower.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Starting adding coalition followers.');

        $this->io->progressStart($this->getCauseFollowersCount());

        $count = 0;
        foreach ($this->getCauseFollowers() as $result) {
            /* @var CauseFollower $causeFollower */
            $causeFollower = $result[0];

            $adherent = $causeFollower->getAdherent();
            $coalition = $causeFollower->getCause()->getCoalition();
            if (!$coalition->hasFollower($adherent)) {
                $coalition->createFollower($adherent);

                $this->em->persist($coalition->createFollower($adherent));
                $this->em->flush();
            }

            if (0 === (++$count % self::BATCH_SIZE)) {
                $this->em->clear();
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success('Coalition followers have been added successfully.');
    }

    private function getCauseFollowers(): IterableResult
    {
        return $this
            ->createCauseFollowerQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function getCauseFollowersCount(): int
    {
        return $this
            ->createCauseFollowerQueryBuilder()
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createCauseFollowerQueryBuilder(): QueryBuilder
    {
        return $this
            ->causeFollowerRepository
            ->createQueryBuilder('follower')
            ->select('follower', 'adherent', 'cause', 'coalition')
            ->distinct()
            ->innerJoin('follower.adherent', 'adherent')
            ->leftJoin('follower.cause', 'cause')
            ->leftJoin('cause.coalition', 'coalition')
            ->leftJoin('coalition.followers', 'cFollower', Join::WITH, 'adherent = cFollower')
            ->where('adherent.coalitionSubscription = :true AND cFollower IS NULL')
            ->setParameter('true', true)
        ;
    }
}

<?php

namespace App\Command;

use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
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
class CreatePoliticalCommitteeFromTerritorialCouncilCommand extends Command
{
    protected static $defaultName = 'app:territorial-council:create-political-committees';

    private $em;
    private $territorialCouncilRepository;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(EntityManagerInterface $em, TerritorialCouncilRepository $territorialCouncilRepository)
    {
        $this->em = $em;
        $this->territorialCouncilRepository = $territorialCouncilRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create Political committees from Territorial councils.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Starting creation of Political committees.');

        $this->io->progressStart($this->getTerritorialCouncilCount());

        foreach ($this->getTerritorialCouncils() as $result) {
            /* @var TerritorialCouncil $territorialCouncil */
            $territorialCouncil = $result[0];

            $politicalCommittee = new PoliticalCommittee($territorialCouncil->getNameCodes(), $territorialCouncil);

            $this->em->persist($politicalCommittee);
            $this->em->flush();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success('Political committee have been created successfully!');
    }

    private function getTerritorialCouncils(): IterableResult
    {
        return $this
            ->createTerritorialCouncilQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function getTerritorialCouncilCount(): int
    {
        return $this
            ->createTerritorialCouncilQueryBuilder()
            ->select('COUNT(tc)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createTerritorialCouncilQueryBuilder(): QueryBuilder
    {
        return $this
            ->territorialCouncilRepository
            ->createQueryBuilder('tc')
        ;
    }
}

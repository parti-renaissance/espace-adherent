<?php

namespace App\Command;

use App\Entity\RepublicanSilence;
use App\Repository\RepublicanSilenceRepository;
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
class MigrateRepublicanSilenceToGeoZonesCommand extends Command
{
    protected static $defaultName = 'app:republican-silence:migrate';

    private EntityManagerInterface $em;
    private RepublicanSilenceRepository $silenceRepository;
    private SymfonyStyle $io;

    public function __construct(EntityManagerInterface $em, RepublicanSilenceRepository $silenceRepository)
    {
        $this->em = $em;
        $this->silenceRepository = $silenceRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate republican silences to geo zones.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Starting republican silence migration.');

        $this->io->progressStart($this->republicanSilenceCount());

        foreach ($this->getRepublicanSilences() as $result) {
            /* @var RepublicanSilence $republicanSilence */
            $republicanSilence = $result[0];
            foreach ($republicanSilence->getReferentTags() as $referentTag) {
                $republicanSilence->addZone($referentTag->getZone());
            }

            $this->em->flush();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success('Republican silence migration has been finished successfully.');

        return 0;
    }

    private function getRepublicanSilences(): IterableResult
    {
        return $this
            ->createRepublicanSilencesQuetyBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function republicanSilenceCount(): int
    {
        return $this
            ->createRepublicanSilencesQuetyBuilder()
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createRepublicanSilencesQuetyBuilder(): QueryBuilder
    {
        return $this
            ->silenceRepository
            ->createQueryBuilder('rs')
        ;
    }
}

<?php

namespace App\Command;

use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Oneshot command.
 */
class CreateUuidsForElectedRepresentativeCommand extends Command
{
    private const BATCH_SIZE = 1000;

    private $electedRepresentativeRepository;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(
        EntityManagerInterface $em,
        ElectedRepresentativeRepository $electedRepresentativeRepository
    ) {
        $this->em = $em;
        $this->electedRepresentativeRepository = $electedRepresentativeRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:elected-representative:create-uuids')
            ->setDescription('Create Uuid for ElectedRepresentative.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->text('Starting uuid creation for ElectedRepresentative.');
        $this->io->progressStart($this->electedRepresentativeRepository->count([]));

        $batch = 0;
        foreach ($this->getElectedRepresentativeIds() as $id) {
            $electedRepresentative = $this->electedRepresentativeRepository->find($id);
            $electedRepresentative->setUuid(Uuid::uuid4());

            ++$batch;
            if (0 === $batch % self::BATCH_SIZE) {
                $this->io->progressAdvance(self::BATCH_SIZE);

                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->io->progressFinish();

        $this->em->flush();

        $this->io->success('Uuid creation for ElectedRepresentatives finished successfully!');
    }

    private function getElectedRepresentativeIds(): array
    {
        return array_column(
            $this->electedRepresentativeRepository->createQueryBuilder('er')
                ->select('er.id')
                ->getQuery()
                ->getArrayResult(),
            'id'
        );
    }
}

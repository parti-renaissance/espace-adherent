<?php

namespace App\Command;

use App\Adherent\Command\AdherentMigrateInterestsCommand;
use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MigrateAdherentInterestsCommand extends Command
{
    private const BATCH_SIZE = 1000;

    protected static $defaultName = 'app:migrate:adherent-interests';

    private $bus;
    private $adherentRepository;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(MessageBusInterface $bus, AdherentRepository $adherentRepository)
    {
        $this->bus = $bus;
        $this->adherentRepository = $adherentRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Migrate adherent interests.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Scheduling adherents interests update.');

        $this->io->progressStart($this->getAdherentsCount());

        foreach ($this->getAdherentsIterator() as $result) {
            /** @var Adherent $adherent */
            $adherent = $result[0];

            $this->bus->dispatch(new AdherentMigrateInterestsCommand($adherent->getUuid()));

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success('Adherents interests update scheduled successfully!');
    }

    private function getAdherentsIterator(): IterableResult
    {
        return $this
            ->createAdherentQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function getAdherentsCount(): int
    {
        return $this
            ->createAdherentQueryBuilder()
            ->select('COUNT(adherent)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createAdherentQueryBuilder(): QueryBuilder
    {
        return $this
            ->adherentRepository
            ->createQueryBuilder('adherent')
        ;
    }
}

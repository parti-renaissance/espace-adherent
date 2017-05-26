<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adherent;
use AppBundle\Referent\ReferentDatabaseDumper;
use AppBundle\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReferentFullDumpCommand extends ContainerAwareCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var AdherentRepository
     */
    private $repository;

    /**
     * @var ReferentDatabaseDumper
     */
    private $dumper;

    protected function configure()
    {
        $this
            ->setName('app:referent:full-dump')
            ->setDescription('Dumps the list of managed users files of all referents')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->io = new SymfonyStyle($input, $output);
        $this->repository = $this->getContainer()->get('doctrine')->getRepository(Adherent::class);
        $this->dumper = $this->getContainer()->get('app.referent.database_dumper');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $referents = $this->repository->findReferents();
        foreach ($referents as $referent) {
            foreach (ReferentDatabaseDumper::EXPORT_TYPES as $type) {
                $this->dump($referent, $type);
            }
        }

        if (!$this->io->isQuiet()) {
            $this->io->success('Done.');
        }
    }

    private function dump(Adherent $referent, string $type): void
    {
        if (!$this->io->isQuiet()) {
            $this->io->writeln(sprintf(
                '%s | Starting database export for referent <comment>%s</comment> (type: <comment>%s</comment>).',
                date('Y-m-d H:i:s'),
                $referent->getEmailAddress(),
                $type
            ));
        }

        $this->dumper->dump($referent->getUuid()->toString(), $type);
    }
}

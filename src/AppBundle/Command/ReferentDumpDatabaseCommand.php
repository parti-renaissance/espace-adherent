<?php

namespace AppBundle\Command;

use AppBundle\Referent\ReferentDatabaseDumper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReferentDumpDatabaseCommand extends ContainerAwareCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var ReferentDatabaseDumper
     */
    private $dumper;

    protected function configure()
    {
        $this
            ->setName('app:referent:single-dump')
            ->setDescription('Dumps the list of managed users, newsletters, etc. files of a single referent')
            ->addArgument('referent', InputArgument::REQUIRED, 'The adherent UUID or email address')
            ->addArgument('type', InputArgument::OPTIONAL, 'The type of files to generate (ie. adherents)')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command dumps the list of users a referent is managing:

  <info>php %command.full_name% adherent@email.tld</info>

The email address argument can also be replaced by the adherent UUID:

  <info>php %command.full_name% 954ec04a-f35f-4a4d-81c8-c3055806610d</info>

With this form, the commands will generate 7 files corresponding to the 7 following types:

* <comment>all</comment>
* <comment>serialized</comment>
* <comment>subscribers</comment>
* <comment>adherents</comment>
* <comment>non_followers</comment>
* <comment>followers</comment>
* <comment>hosts</comment>

The command also accepts a second optional argument, which is the type of files to generate.

  <info>php %command.full_name% 954ec04a-f35f-4a4d-81c8-c3055806610d serialized</info>
  <info>php %command.full_name% adherent@email.tld adherents</info>

EOF
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->io = new SymfonyStyle($input, $output);
        $this->dumper = $this->getContainer()->get('app.referent.database_dumper');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (empty($types = (array) $input->getArgument('type'))) {
            $types = ReferentDatabaseDumper::EXPORT_TYPES;
        }

        $referent = $input->getArgument('referent');
        foreach ($types as $type) {
            $this->dump($referent, $type);
        }

        if (!$this->io->isQuiet()) {
            $this->io->success('Done.');
        }
    }

    private function dump(string $referent, string $type): void
    {
        if (!$this->io->isQuiet()) {
            $this->io->writeln(sprintf('Starting database export for referent <comment>%s</comment> (type: <comment>%s</comment>).', $referent, $type));
        }

        $this->dumper->dump($referent, $type);
    }
}

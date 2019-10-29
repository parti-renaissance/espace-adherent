<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DonationMigrateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('app:donations:migrate')
            ->setDescription('Migrate Donations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['', 'Donations migrated successfully!']);
    }
}

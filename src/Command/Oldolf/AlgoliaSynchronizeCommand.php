<?php

namespace AppBundle\Command\Oldolf;

use AppBundle\Command\AlgoliaSynchronizeCommand as BaseCommand;
use AppBundle\Entity\Oldolf\City;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AlgoliaSynchronizeCommand extends BaseCommand
{
    protected const ENTITIES_TO_INDEX = [
        City::class,
    ];

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('app:oldolf:algolia-synchronize')
            ->setDescription('Synchronize Timeline indices on Algolia')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Synchronizing OLDOLF with Algolia..');

        parent::execute($input, $output);

        $output->writeln('OLDOLF has been successfully synchronized with Algolia. (but the tasks may not have completed on Algolia\'s side yet)');
    }
}

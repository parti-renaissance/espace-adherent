<?php

namespace AppBundle\Command;

use AppBundle\Entity\Timeline\Manifesto;
use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\Profile;
use AppBundle\Entity\Timeline\Theme;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TimelineSynchronizeCommand extends AlgoliaSynchronizeCommand
{
    protected const ENTITIES_TO_INDEX = [
        Profile::class,
        Manifesto::class,
        Theme::class,
        Measure::class,
    ];

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('app:timeline:synchronize')
            ->setDescription('Synchronize Timeline indices on Algolia')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Synchronizing Timeline with Algolia..');

        parent::execute($input, $output);

        $output->writeln('Timeline has been successfully synchronized with Algolia. (but the tasks may not have completed on Algolia\'s side yet)');
    }
}

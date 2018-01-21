<?php

namespace AppBundle\Command;

use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\Profile;
use AppBundle\Entity\Timeline\Theme;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TimelineSynchronizeCommand extends Command
{
    private const ENTITIES_TO_INDEX = [
        Profile::class,
        Theme::class,
        Measure::class,
    ];

    /**
     * @var AlgoliaSynchronizeCommand
     */
    private $synchronizeCommand;

    public function __construct(AlgoliaSynchronizeCommand $synchronizeCommand)
    {
        $this->synchronizeCommand = $synchronizeCommand;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:timeline:synchronize')
            ->setDescription('Update Timeline indices on Algolia')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Synchronizing Timeline with Algolia..');

        foreach (self::ENTITIES_TO_INDEX as $entityToIndex) {
            $this->synchronizeCommand->run(new ArrayInput(['entityName' => $entityToIndex]), $output);
        }

        $output->writeln('Timeline has been successfully synchronized with Algolia. (but the tasks may not have completed on Algolia\'s side yet)');
    }
}

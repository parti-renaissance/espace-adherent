<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GlideClearCacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:glide:purge')
            ->addArgument('path', InputArgument::REQUIRED)
            ->setDescription('Clear the Glide cache for a given path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('app.glide')->deleteCache($input->getArgument('path'));

        $output->writeln('Done');
    }
}

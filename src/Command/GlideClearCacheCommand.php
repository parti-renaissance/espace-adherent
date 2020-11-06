<?php

namespace App\Command;

use League\Glide\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GlideClearCacheCommand extends Command
{
    /** @var Server */
    private $glide;

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
        $this->glide->deleteCache($input->getArgument('path'));

        $output->writeln('Done');
    }

    /** @required */
    public function setGlide(Server $glide): void
    {
        $this->glide = $glide;
    }
}

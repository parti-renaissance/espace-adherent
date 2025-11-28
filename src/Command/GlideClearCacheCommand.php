<?php

declare(strict_types=1);

namespace App\Command;

use League\Glide\Server;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'app:glide:purge',
    description: 'Clear the Glide cache for a given path'
)]
class GlideClearCacheCommand extends Command
{
    /** @var Server */
    private $glide;

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->glide->deleteCache($input->getArgument('path'));

        $output->writeln('Done');

        return self::SUCCESS;
    }

    #[Required]
    public function setGlide(Server $glide): void
    {
        $this->glide = $glide;
    }
}

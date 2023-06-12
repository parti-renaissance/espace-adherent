<?php

namespace App\Command;

use App\Deputy\DistrictLoader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:deputy-districts:import',
)]
class ImportDistrictsCommand extends Command
{
    private $loader;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(DistrictLoader $loader)
    {
        $this->loader = $loader;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'CSV file of all districts to load');
        $this->addArgument('districtsFile', InputArgument::REQUIRED, 'GeoJSON file of french districts to load');
        $this->addArgument('countriesFile', InputArgument::REQUIRED, 'GeoJSON file of countries to load');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->text('Start importing districts');

        $this->loader->load(
            $input->getArgument('file'),
            $input->getArgument('districtsFile'),
            $input->getArgument('countriesFile')
        );

        $this->io->success('Done');

        return self::SUCCESS;
    }
}

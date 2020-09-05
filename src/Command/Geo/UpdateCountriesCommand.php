<?php

namespace App\Command\Geo;

use App\Command\Geo\Helper\Persister;
use App\Command\Geo\Helper\Summary;
use App\Entity\Geo\Country;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Intl\Countries;

final class UpdateCountriesCommand extends Command
{
    protected static $defaultName = 'app:geo:update-countries';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var ArrayCollection
     */
    private $entities;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Update countries')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm without persisting any data.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->entities = new ArrayCollection();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Start updating countries');

        $repository = $this->em->getRepository(Country::class);

        $names = Countries::getNames('fr');
        foreach ($names as $code => $name) {
            $country = $repository->findOneBy(['code' => $code]) ?: new Country($code, $name);
            $country->setName($name);
            $country->activate(true);
            $this->entities->add($country);
        }

        $summary = new Summary($this->io);
        $summary->run($this->entities);

        $persister = new Persister($this->io, $this->em);
        $persister->run($this->entities, $input->getOption('dry-run'));

        return 0;
    }
}

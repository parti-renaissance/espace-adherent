<?php

namespace App\Command;

use App\Algolia\ManualIndexer;
use App\Entity\Article;
use App\Entity\Clarification;
use App\Entity\CustomSearchResult;
use App\Entity\Event;
use App\Entity\Page;
use App\Entity\Proposal;
use App\Entity\Timeline\Manifesto;
use App\Entity\Timeline\Measure;
use App\Entity\Timeline\Profile;
use App\Entity\Timeline\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AlgoliaSynchronizeCommand extends Command
{
    public const COMMAND_NAME = 'app:algolia:synchronize';

    protected const ENTITIES_TO_INDEX = [
        Article::class,
        Proposal::class,
        Clarification::class,
        CustomSearchResult::class,
        Event::class,
        Profile::class,
        Manifesto::class,
        Theme::class,
        Measure::class,
        Page::class,
    ];

    private $algolia;
    private $manager;

    public function __construct(ManualIndexer $algolia, EntityManagerInterface $manager)
    {
        $this->algolia = $algolia;
        $this->manager = $manager;

        $filters = $this->manager->getFilters();

        if ($filters->isEnabled('oneLocale')) {
            $filters->disable('oneLocale');
        }

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->addArgument('entityName', InputArgument::OPTIONAL, 'Which type of entity do you want to reindex? If not set, all is assumed.')
            ->setDescription('Synchronize indices on Algolia')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getEntitiesToIndex($input->getArgument('entityName')) as $entity) {
            $output->write("Synchronizing entity $entity ... ");
            $nbIndexes = $this->algolia->reIndex($entity);
            $output->writeln("done, $nbIndexes records indexed");
        }
    }

    private function getEntitiesToIndex(?string $entityName): array
    {
        if ($entityName) {
            return [$this->manager->getRepository($entityName)->getClassName()];
        }

        return static::ENTITIES_TO_INDEX;
    }
}

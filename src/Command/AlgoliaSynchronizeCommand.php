<?php

namespace AppBundle\Command;

use Algolia\AlgoliaSearchBundle\Indexer\ManualIndexer;
use AppBundle\Entity\Article;
use AppBundle\Entity\Clarification;
use AppBundle\Entity\CustomSearchResult;
use AppBundle\Entity\Event;
use AppBundle\Entity\Proposal;
use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\Profile;
use AppBundle\Entity\Timeline\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AlgoliaSynchronizeCommand extends Command
{
    public const COMMAND_NAME = 'app:algolia:synchronize';

    private const ENTITIES_TO_INDEX = [
        Article::class,
        Proposal::class,
        Clarification::class,
        CustomSearchResult::class,
        Event::class,
        Profile::class,
        Theme::class,
        Measure::class,
    ];

    /**
     * @var ManualIndexer
     */
    private $indexer;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(ManualIndexer $indexer, EntityManagerInterface $manager)
    {
        $this->indexer = $indexer;
        $this->manager = $manager;

        $this->manager->getFilters()->disable('oneLocale');

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->addArgument('entityName', InputArgument::OPTIONAL, 'Which type of entity do you want to reindex? If not set, all is assumed.')
            ->setDescription('Synchronize')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityNameToIndex = $input->getArgument('entityName');
        $toIndex = $entityNameToIndex ? [$this->manager->getRepository($entityNameToIndex)->getClassName()] : self::ENTITIES_TO_INDEX;

        foreach ($toIndex as $entity) {
            $output->write('Synchronizing entity '.$entity.' ... ');
            $nbIndexes = $this->synchronizeEntity($entity);
            $output->writeln('done, '.$nbIndexes.' records indexed');
        }
    }

    private function synchronizeEntity($className)
    {
        return (int) $this->indexer->reIndex($className, [
            'batchSize' => 3000,
            'safe' => true,
            'clearEntityManager' => true,
        ]);
    }
}

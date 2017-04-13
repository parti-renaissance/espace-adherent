<?php

namespace AppBundle\Command;

use Algolia\AlgoliaSearchBundle\Indexer\ManualIndexer;
use AlgoliaSearch\Client;
use AppBundle\Entity\Article;
use AppBundle\Entity\Clarification;
use AppBundle\Entity\Page;
use AppBundle\Entity\Proposal;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AlgoliaSynchronizeCommand extends ContainerAwareCommand
{
    const ENTITIES_TO_INDEX = [
        Article::class,
        Proposal::class,
        Clarification::class,
        Page::class,
    ];

    const STATIC_PAGES_TO_INDEX = [
        [
            'title' => 'Elles Marchent',
            'keywords' => 'égalité homme femme féminisme Marlène Schiappa Zineb Mekouar Axelle Tessandier',
            'description' => 'Aujourd\'hui le combat pour l\'égalité réelle femme / homme est culturel. Les lois existent, '.
                'c\'est maintenant aux mentalités d\'évoluer. Ce sera la grande cause nationale du quinquennat '.
                'd\'Emmanuel Macron.',
            'image' => 'elles-marchent',
            'url' => '/elles-marchent',
            'static' => true,
        ],
        [
            'title' => 'Diago, le Bot En Marche !',
            'keywords' => 'bot robot messenger diago',
            'description' => 'Découvrez Diago, votre assistant En Marche !',
            'image' => 'default',
            'url' => '/bot',
            'static' => true,
        ],
        [
            'title' => 'La carte des comités',
            'keywords' => 'mouvement carte interactive comités mapmonde',
            'description' => 'La carte des comités',
            'image' => 'default',
            'url' => '/le-mouvement/la-carte',
            'static' => true,
        ],
        [
            'title' => 'La carte des événements',
            'keywords' => 'mouvement carte interactive événements mapmonde',
            'description' => 'La carte des événements',
            'image' => 'default',
            'url' => '/evenements/la-carte',
            'static' => true,
        ],
        [
            'title' => 'J\'agis',
            'keywords' => 'agir inviter convaincre partager devenir relais volontaire action organiser',
            'description' => 'Emmanuel Macron a besoin de vous pour convaincre les indécis ! '.
                'Convaincre 10 personnes, collecter 10 e-mails ou donner 10 euros, chacun peut aider à sa '.'
                façon pour faire gagner les idées du progrès.',
            'image' => 'default',
            'url' => '/jagis',
            'static' => true,
        ],
        [
            'title' => 'Je partage le programme',
            'keywords' => 'partager projet voter macron réseaux sociaux',
            'description' => 'Partagez le projet',
            'image' => 'default',
            'url' => '/jepartage',
            'static' => true,
        ],
        [
            'title' => 'Pourquoi voter Macron',
            'keywords' => 'convaincre proches email argumentaire personnalisé ami connaissance',
            'description' => 'Aidez à convaincre vos proches en leur envoyant les propositions qui les concernent !',
            'image' => 'pourquoi-voter-macron',
            'url' => '/pourquoivotermacron',
            'static' => true,
        ],
        [
            'title' => 'Donner ou recevoir procuration',
            'keywords' => 'mandant mandataire voter donner recevoir procuration élection présent date',
            'description' => 'Chaque vote compte. Donnez ou recevez procuration avec En Marche !',
            'image' => 'procuration',
            'url' => '/procuration',
            'static' => true,
        ],
    ];

    /**
     * @var string
     */
    private $env;

    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var ManualIndexer
     */
    private $indexer;

    /**
     * @var Client
     */
    private $client;

    protected function configure()
    {
        $this
            ->setName('app:algolia:synchronize')
            ->addArgument('entityName', InputArgument::OPTIONAL, 'Which type of entity do you want to reindex? If not set, all is assumed.')
            ->setDescription('Synchronize')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $algolia = $this->getContainer()->get('algolia.indexer');

        $this->env = $this->getContainer()->getParameter('kernel.environment');
        $this->manager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->indexer = $algolia->getManualIndexer($this->manager);
        $this->client = $algolia->getClient();
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
        $nbIndexed = (int) $this->indexer->reIndex($className, [
            'batchSize' => 3000,
            'safe' => true,
            'clearEntityManager' => true,
        ]);

        if ($className === Page::class) {
            $index = $this->client->initIndex('Page_'.$this->env);
            $index->addObjects(self::STATIC_PAGES_TO_INDEX);

            $nbIndexed += count(self::STATIC_PAGES_TO_INDEX);
        }

        return $nbIndexed;
    }
}
